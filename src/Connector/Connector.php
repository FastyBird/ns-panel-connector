<?php declare(strict_types = 1);

/**
 * Connector.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Connector
 * @since          1.0.0
 *
 * @date           09.07.23
 */

namespace FastyBird\Connector\NsPanel\Connector;

use FastyBird\Connector\NsPanel;
use FastyBird\Connector\NsPanel\Clients;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Helpers;
use FastyBird\Connector\NsPanel\Queue;
use FastyBird\Connector\NsPanel\Servers;
use FastyBird\Connector\NsPanel\Writers;
use FastyBird\Library\Bootstrap\Helpers as BootstrapHelpers;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Connectors as DevicesConnectors;
use FastyBird\Module\Devices\Entities as DevicesEntities;
use FastyBird\Module\Devices\Events as DevicesEvents;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use Nette;
use Psr\EventDispatcher as PsrEventDispatcher;
use React\EventLoop;
use ReflectionClass;
use function array_key_exists;
use function assert;
use function React\Async\async;

/**
 * Connector service executor
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Connector
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Connector implements DevicesConnectors\Connector
{

	use Nette\SmartObject;

	private const QUEUE_PROCESSING_INTERVAL = 0.01;

	/** @var array<Clients\Client|Clients\Discovery> */
	private array $clients = [];

	private Servers\Server|null $server = null;

	private Writers\Writer|null $writer = null;

	private EventLoop\TimerInterface|null $consumersTimer = null;

	/**
	 * @param array<Clients\ClientFactory> $clientsFactories
	 */
	public function __construct(
		private readonly DevicesEntities\Connectors\Connector $connector,
		private readonly array $clientsFactories,
		private readonly Clients\DiscoveryFactory $discoveryClientFactory,
		private readonly Helpers\Entity $entityHelper,
		private readonly Helpers\Connector $connectorHelper,
		private readonly Helpers\Devices\ThirdPartyDevice $thirdPartyDeviceHelper,
		private readonly Queue\Queue $queue,
		private readonly Queue\Consumers $consumers,
		private readonly Servers\ServerFactory $serverFactory,
		private readonly Writers\WriterFactory $writerFactory,
		private readonly NsPanel\Logger $logger,
		private readonly DevicesModels\Configuration\Connectors\Repository $connectorsConfigurationRepository,
		private readonly DevicesModels\Configuration\Devices\Repository $devicesConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Repository $channelsConfigurationRepository,
		private readonly EventLoop\LoopInterface $eventLoop,
		private readonly PsrEventDispatcher\EventDispatcherInterface|null $dispatcher = null,
	)
	{
	}

	/**
	 * @throws DevicesExceptions\InvalidState
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 */
	public function execute(): void
	{
		assert($this->connector instanceof Entities\NsPanelConnector);

		$mode = $this->connector->getClientMode();

		$this->logger->info(
			'Starting NS Panel connector service',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'connector',
				'connector' => [
					'id' => $this->connector->getId()->toString(),
				],
			],
		);

		$findConnectorQuery = new DevicesQueries\Configuration\FindConnectors();
		$findConnectorQuery->byId($this->connector->getId());
		$findConnectorQuery->byType(Entities\NsPanelConnector::TYPE);

		$connector = $this->connectorsConfigurationRepository->findOneBy($findConnectorQuery);

		if ($connector === null) {
			$this->logger->error(
				'Connector could not be loaded',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'connector',
					'connector' => [
						'id' => $this->connector->getId()->toString(),
					],
				],
			);

			return;
		}

		foreach ($this->clientsFactories as $clientFactory) {
			$rc = new ReflectionClass($clientFactory);

			$constants = $rc->getConstants();

			if (
				(
					array_key_exists(Clients\ClientFactory::MODE_CONSTANT_NAME, $constants)
					&& $mode->equalsValue($constants[Clients\ClientFactory::MODE_CONSTANT_NAME])
				) || $mode->equalsValue(NsPanel\Types\ClientMode::BOTH)
			) {
				$client = $clientFactory->create($connector);
				$client->connect();

				$this->clients[] = $client;
			}
		}

		if (
			$this->connectorHelper->getClientMode($connector)->equalsValue(NsPanel\Types\ClientMode::BOTH)
			|| $this->connectorHelper->getClientMode($connector)->equalsValue(NsPanel\Types\ClientMode::DEVICE)
		) {
			$this->server = $this->serverFactory->create($connector);
			$this->server->connect();
		}

		$this->writer = $this->writerFactory->create($connector);
		$this->writer->connect();

		$this->consumersTimer = $this->eventLoop->addPeriodicTimer(
			self::QUEUE_PROCESSING_INTERVAL,
			async(function (): void {
				$this->consumers->consume();
			}),
		);

		if (
			$mode->equalsValue(NsPanel\Types\ClientMode::BOTH)
			|| $mode->equalsValue(NsPanel\Types\ClientMode::DEVICE)
		) {
			$this->eventLoop->addTimer(1, function () use ($connector): void {
				$findDevicesQuery = new DevicesQueries\Configuration\FindDevices();
				$findDevicesQuery->forConnector($connector);
				$findDevicesQuery->byType(Entities\Devices\ThirdPartyDevice::TYPE);

				$devices = $this->devicesConfigurationRepository->findAllBy($findDevicesQuery);

				foreach ($devices as $device) {
					$findChannelsQuery = new DevicesQueries\Configuration\FindChannels();
					$findChannelsQuery->forDevice($device);

					$channels = $this->channelsConfigurationRepository->findAllBy($findChannelsQuery);

					foreach ($channels as $channel) {
						try {
							$this->queue->append(
								$this->entityHelper->create(
									Entities\Messages\WriteThirdPartyDeviceState::class,
									[
										'connector' => $device->getConnector(),
										'device' => $device->getId(),
										'channel' => $channel->getId(),
									],
								),
							);
						} catch (Exceptions\Runtime $ex) {
							$this->logger->error(
								'Could report device initial state to NS Panel',
								[
									'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
									'type' => 'connector',
									'exception' => BootstrapHelpers\Logger::buildException($ex),
									'connector' => [
										'id' => $this->connector->getId()->toString(),
									],
									'gateway' => [
										'id' => $this->thirdPartyDeviceHelper->getGateway($device)->getId()->toString(),
									],
									'device' => [
										'id' => $device->getId()->toString(),
									],
									'channel' => [
										'id' => $channel->getId()->toString(),
									],
								],
							);
						}
					}
				}
			});
		}

		$this->logger->info(
			'NS Panel connector service has been started',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'connector',
				'connector' => [
					'id' => $this->connector->getId()->toString(),
				],
			],
		);
	}

	/**
	 * @throws DevicesExceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 */
	public function discover(): void
	{
		assert($this->connector instanceof Entities\NsPanelConnector);

		$this->logger->info(
			'Starting NS Panel connector discovery',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'connector',
				'connector' => [
					'id' => $this->connector->getId()->toString(),
				],
			],
		);

		$client = $this->discoveryClientFactory->create($this->connector);

		$client->on('finished', function (): void {
			$this->dispatcher?->dispatch(
				new DevicesEvents\TerminateConnector(
					MetadataTypes\ConnectorSource::get(MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL),
					'Devices discovery finished',
				),
			);
		});

		$this->clients[] = $client;

		$this->consumersTimer = $this->eventLoop->addPeriodicTimer(
			self::QUEUE_PROCESSING_INTERVAL,
			async(function (): void {
				$this->consumers->consume();
			}),
		);

		$this->logger->info(
			'NS Panel connector discovery has been started',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'connector',
				'connector' => [
					'id' => $this->connector->getId()->toString(),
				],
			],
		);

		$client->discover();
	}

	public function terminate(): void
	{
		foreach ($this->clients as $client) {
			$client->disconnect();
		}

		$this->server?->disconnect();

		$this->writer?->disconnect();

		if ($this->consumersTimer !== null && $this->queue->isEmpty()) {
			$this->eventLoop->cancelTimer($this->consumersTimer);
		}

		$this->logger->info(
			'NS Panel connector has been terminated',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'connector',
				'connector' => [
					'id' => $this->connector->getId()->toString(),
				],
			],
		);
	}

	public function hasUnfinishedTasks(): bool
	{
		return !$this->queue->isEmpty() && $this->consumersTimer !== null;
	}

}
