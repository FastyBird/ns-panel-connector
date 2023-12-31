<?php declare(strict_types = 1);

/**
 * WriteSubDeviceState.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           18.07.23
 */

namespace FastyBird\Connector\NsPanel\Queue\Consumers;

use DateTimeInterface;
use FastyBird\Connector\NsPanel;
use FastyBird\Connector\NsPanel\API;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Helpers;
use FastyBird\Connector\NsPanel\Queue;
use FastyBird\DateTimeFactory;
use FastyBird\Library\Bootstrap\Helpers as BootstrapHelpers;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use FastyBird\Module\Devices\States as DevicesStates;
use FastyBird\Module\Devices\Utilities as DevicesUtilities;
use Nette;
use Nette\Utils;
use Throwable;
use function array_merge;
use function strval;

/**
 * Write state to sub-device message consumer
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class WriteSubDeviceState implements Queue\Consumer
{

	use StateWriter;
	use Nette\SmartObject;

	private API\LanApi|null $lanApiApi = null;

	public function __construct(
		protected readonly Helpers\Channel $channelHelper,
		protected readonly DevicesModels\Configuration\Channels\Properties\Repository $channelsPropertiesConfigurationRepository,
		protected readonly DevicesUtilities\ChannelPropertiesStates $channelPropertiesStatesManager,
		private readonly Queue\Queue $queue,
		private readonly API\LanApiFactory $lanApiApiFactory,
		private readonly Helpers\Entity $entityHelper,
		private readonly Helpers\Devices\Gateway $gatewayHelper,
		private readonly Helpers\Devices\SubDevice $subDeviceHelper,
		private readonly NsPanel\Logger $logger,
		private readonly DevicesModels\Configuration\Connectors\Repository $connectorsConfigurationRepository,
		private readonly DevicesModels\Configuration\Devices\Repository $devicesConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Repository $channelsConfigurationRepository,
		private readonly DateTimeFactory\Factory $dateTimeFactory,
	)
	{
	}

	/**
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\Runtime
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function consume(Entities\Messages\Entity $entity): bool
	{
		if (!$entity instanceof Entities\Messages\WriteSubDeviceState) {
			return false;
		}

		$findConnectorQuery = new DevicesQueries\Configuration\FindConnectors();
		$findConnectorQuery->byId($entity->getConnector());

		$connector = $this->connectorsConfigurationRepository->findOneBy($findConnectorQuery);

		if ($connector === null) {
			$this->logger->error(
				'Connector could not be loaded',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		$findDeviceQuery = new DevicesQueries\Configuration\FindDevices();
		$findDeviceQuery->forConnector($connector);
		$findDeviceQuery->byId($entity->getDevice());
		$findDeviceQuery->byType(Entities\Devices\SubDevice::TYPE);

		$device = $this->devicesConfigurationRepository->findOneBy($findDeviceQuery);

		if ($device === null) {
			$this->logger->error(
				'Device could not be loaded',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		$gateway = $this->subDeviceHelper->getGateway($device);

		$ipAddress = $this->gatewayHelper->getIpAddress($gateway);
		$accessToken = $this->gatewayHelper->getAccessToken($gateway);

		if ($ipAddress === null || $accessToken === null) {
			$this->queue->append(
				$this->entityHelper->create(
					Entities\Messages\StoreDeviceConnectionState::class,
					[
						'connector' => $connector->getId(),
						'identifier' => $gateway->getIdentifier(),
						'state' => MetadataTypes\ConnectionState::STATE_ALERT,
					],
				),
			);

			$this->logger->error(
				'Device owning NS Panel is not configured',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		$findChannelQuery = new DevicesQueries\Configuration\FindChannels();
		$findChannelQuery->forDevice($device);
		$findChannelQuery->byId($entity->getChannel());

		$channel = $this->channelsConfigurationRepository->findOneBy($findChannelQuery);

		if ($channel === null) {
			$this->logger->error(
				'Channel could not be loaded',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		if (!$this->channelHelper->getCapability($channel)->hasReadWritePermission()) {
			$this->logger->error(
				'Device state is not writable',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		$mapped = $this->mapChannelToState($channel);

		if ($mapped === null) {
			$this->logger->error(
				'Device state could not be created',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		try {
			$this->getApiClient($connector)->setSubDeviceState(
				$device->getIdentifier(),
				$mapped,
				$ipAddress,
				$accessToken,
			)
				->then(function () use ($channel): void {
					$now = $this->dateTimeFactory->getNow();

					$findPropertiesQuery = new DevicesQueries\Configuration\FindChannelDynamicProperties();
					$findPropertiesQuery->forChannel($channel);
					$findPropertiesQuery->settable(true);

					$properties = $this->channelsPropertiesConfigurationRepository->findAllBy(
						$findPropertiesQuery,
						MetadataDocuments\DevicesModule\ChannelDynamicProperty::class,
					);

					foreach ($properties as $property) {
						$state = $this->channelPropertiesStatesManager->getValue($property);

						if ($state?->getExpectedValue() !== null) {
							$this->channelPropertiesStatesManager->setValue(
								$property,
								Utils\ArrayHash::from([
									DevicesStates\Property::PENDING_FIELD => $now->format(DateTimeInterface::ATOM),
								]),
							);
						}
					}
				})
				->catch(function (Throwable $ex) use ($entity, $connector, $gateway, $channel): void {
					$findPropertiesQuery = new DevicesQueries\Configuration\FindChannelDynamicProperties();
					$findPropertiesQuery->forChannel($channel);
					$findPropertiesQuery->settable(true);

					$properties = $this->channelsPropertiesConfigurationRepository->findAllBy(
						$findPropertiesQuery,
						MetadataDocuments\DevicesModule\ChannelDynamicProperty::class,
					);

					foreach ($properties as $property) {
						$this->channelPropertiesStatesManager->setValue(
							$property,
							Utils\ArrayHash::from([
								DevicesStates\Property::EXPECTED_VALUE_FIELD => null,
								DevicesStates\Property::PENDING_FIELD => false,
							]),
						);
					}

					$extra = [];

					if ($ex instanceof Exceptions\LanApiCall) {
						$extra = [
							'request' => [
								'method' => $ex->getRequest()?->getMethod(),
								'url' => $ex->getRequest() !== null ? strval($ex->getRequest()->getUri()) : null,
								'body' => $ex->getRequest()?->getBody()->getContents(),
							],
							'response' => [
								'body' => $ex->getResponse()?->getBody()->getContents(),
							],
						];

						$this->queue->append(
							$this->entityHelper->create(
								Entities\Messages\StoreDeviceConnectionState::class,
								[
									'connector' => $connector->getId(),
									'identifier' => $gateway->getIdentifier(),
									'state' => MetadataTypes\ConnectionState::STATE_DISCONNECTED,
								],
							),
						);

					} else {
						$this->queue->append(
							$this->entityHelper->create(
								Entities\Messages\StoreDeviceConnectionState::class,
								[
									'connector' => $connector->getId(),
									'identifier' => $gateway->getIdentifier(),
									'state' => MetadataTypes\ConnectionState::STATE_LOST,
								],
							),
						);
					}

					$this->logger->error(
						'Could write state to sub-device',
						array_merge(
							[
								'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
								'type' => 'write-sub-device-state-message-consumer',
								'exception' => BootstrapHelpers\Logger::buildException($ex),
								'connector' => [
									'id' => $entity->getConnector()->toString(),
								],
								'device' => [
									'id' => $entity->getDevice()->toString(),
								],
								'channel' => [
									'id' => $entity->getChannel()->toString(),
								],
								'data' => $entity->toArray(),
							],
							$extra,
						),
					);
				});
		} catch (Throwable $ex) {
			$this->logger->error(
				'An unhandled error occurred',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'write-sub-device-state-message-consumer',
					'exception' => BootstrapHelpers\Logger::buildException($ex),
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'id' => $entity->getDevice()->toString(),
					],
					'channel' => [
						'id' => $entity->getChannel()->toString(),
					],
					'data' => $entity->toArray(),
				],
			);
		}

		$this->logger->debug(
			'Consumed write sub-device state message',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'write-sub-device-state-message-consumer',
				'connector' => [
					'id' => $entity->getConnector()->toString(),
				],
				'device' => [
					'id' => $entity->getDevice()->toString(),
				],
				'channel' => [
					'id' => $entity->getChannel()->toString(),
				],
				'data' => $entity->toArray(),
			],
		);

		return true;
	}

	private function getApiClient(MetadataDocuments\DevicesModule\Connector $connector): API\LanApi
	{
		if ($this->lanApiApi === null) {
			$this->lanApiApi = $this->lanApiApiFactory->create($connector->getIdentifier());
		}

		return $this->lanApiApi;
	}

}
