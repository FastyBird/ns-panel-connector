<?php declare(strict_types = 1);

/**
 * SetDeviceConnectionState.php
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

use Doctrine\DBAL;
use FastyBird\Connector\NsPanel;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Queue;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use FastyBird\Module\Devices\Utilities as DevicesUtilities;
use Nette;

/**
 * Store device connection state message consumer
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class StoreDeviceConnectionState implements Queue\Consumer
{

	use Nette\SmartObject;

	public function __construct(
		private readonly NsPanel\Logger $logger,
		private readonly DevicesModels\Configuration\Devices\Repository $devicesConfigurationRepository,
		private readonly DevicesModels\Configuration\Devices\Properties\Repository $devicesPropertiesConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Repository $channelsConfigurationRepository,
		private readonly DevicesModels\Configuration\Channels\Properties\Repository $channelsPropertiesConfigurationRepository,
		private readonly DevicesUtilities\DeviceConnection $deviceConnectionManager,
		private readonly DevicesUtilities\DevicePropertiesStates $devicePropertiesStatesManager,
		private readonly DevicesUtilities\ChannelPropertiesStates $channelPropertiesStatesManager,
	)
	{
	}

	/**
	 * @throws DBAL\Exception
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws DevicesExceptions\Runtime
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function consume(Entities\Messages\Entity $entity): bool
	{
		if (!$entity instanceof Entities\Messages\StoreDeviceConnectionState) {
			return false;
		}

		$findDeviceQuery = new DevicesQueries\Configuration\FindDevices();
		$findDeviceQuery->byConnectorId($entity->getConnector());
		$findDeviceQuery->byIdentifier($entity->getIdentifier());

		$device = $this->devicesConfigurationRepository->findOneBy($findDeviceQuery);

		if ($device === null) {
			$this->logger->error(
				'Device could not be loaded',
				[
					'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
					'type' => 'store-device-connection-state-message-consumer',
					'connector' => [
						'id' => $entity->getConnector()->toString(),
					],
					'device' => [
						'identifier' => $entity->getIdentifier(),
					],
					'data' => $entity->toArray(),
				],
			);

			return true;
		}

		// Check device state...
		if (
			!$this->deviceConnectionManager->getState($device)->equals($entity->getState())
		) {
			// ... and if it is not ready, set it to ready
			$this->deviceConnectionManager->setState(
				$device,
				$entity->getState(),
			);

			if (
				$entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_DISCONNECTED)
				|| $entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_ALERT)
				|| $entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_UNKNOWN)
			) {
				$findDevicePropertiesQuery = new DevicesQueries\Configuration\FindDeviceDynamicProperties();
				$findDevicePropertiesQuery->forDevice($device);

				$properties = $this->devicesPropertiesConfigurationRepository->findAllBy(
					$findDevicePropertiesQuery,
					MetadataDocuments\DevicesModule\DeviceDynamicProperty::class,
				);

				foreach ($properties as $property) {
					$this->devicePropertiesStatesManager->setValidState($property, false);
				}

				$findChannelsQuery = new DevicesQueries\Configuration\FindChannels();
				$findChannelsQuery->forDevice($device);

				$channels = $this->channelsConfigurationRepository->findAllBy($findChannelsQuery);

				foreach ($channels as $channel) {
					$findChannelPropertiesQuery = new DevicesQueries\Configuration\FindChannelDynamicProperties();
					$findChannelPropertiesQuery->forChannel($channel);

					$properties = $this->channelsPropertiesConfigurationRepository->findAllBy(
						$findChannelPropertiesQuery,
						MetadataDocuments\DevicesModule\ChannelDynamicProperty::class,
					);

					foreach ($properties as $property) {
						$this->channelPropertiesStatesManager->setValidState($property, false);
					}
				}
			}

			if ($device->getType() === Entities\Devices\Gateway::TYPE) {
				if (
					$entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_DISCONNECTED)
					|| $entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_ALERT)
					|| $entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_UNKNOWN)
				) {
					$findChildrenDevicesQuery = new DevicesQueries\Configuration\FindDevices();
					$findChildrenDevicesQuery->forParent($device);
					$findChildrenDevicesQuery->byType(Entities\Devices\SubDevice::TYPE);

					$children = $this->devicesConfigurationRepository->findAllBy($findChildrenDevicesQuery);

					foreach ($children as $child) {
						$this->deviceConnectionManager->setState(
							$child,
							$entity->getState(),
						);

						$findDevicePropertiesQuery = new DevicesQueries\Configuration\FindDeviceDynamicProperties();
						$findDevicePropertiesQuery->forDevice($child);

						$properties = $this->devicesPropertiesConfigurationRepository->findAllBy(
							$findDevicePropertiesQuery,
							MetadataDocuments\DevicesModule\DeviceDynamicProperty::class,
						);

						foreach ($properties as $property) {
							$this->devicePropertiesStatesManager->setValidState($property, false);
						}

						$findChannelsQuery = new DevicesQueries\Configuration\FindChannels();
						$findChannelsQuery->forDevice($child);

						$channels = $this->channelsConfigurationRepository->findAllBy($findChannelsQuery);

						foreach ($channels as $channel) {
							$findChannelPropertiesQuery = new DevicesQueries\Configuration\FindChannelDynamicProperties();
							$findChannelPropertiesQuery->forChannel($channel);

							$properties = $this->channelsPropertiesConfigurationRepository->findAllBy(
								$findChannelPropertiesQuery,
								MetadataDocuments\DevicesModule\ChannelDynamicProperty::class,
							);

							foreach ($properties as $property) {
								$this->channelPropertiesStatesManager->setValidState($property, false);
							}
						}
					}
				}

				if ($entity->getState()->equalsValue(MetadataTypes\ConnectionState::STATE_ALERT)) {
					$findChildrenDevicesQuery = new DevicesQueries\Configuration\FindDevices();
					$findChildrenDevicesQuery->forParent($device);
					$findChildrenDevicesQuery->byType(Entities\Devices\ThirdPartyDevice::TYPE);

					$children = $this->devicesConfigurationRepository->findAllBy($findChildrenDevicesQuery);

					foreach ($children as $child) {
						$this->deviceConnectionManager->setState(
							$child,
							$entity->getState(),
						);
					}
				}
			}
		}

		$this->logger->debug(
			'Consumed device connection state message',
			[
				'source' => MetadataTypes\ConnectorSource::SOURCE_CONNECTOR_NS_PANEL,
				'type' => 'store-device-connection-state-message-consumer',
				'connector' => [
					'id' => $entity->getConnector()->toString(),
				],
				'device' => [
					'identifier' => $entity->getIdentifier(),
				],
				'data' => $entity->toArray(),
			],
		);

		return true;
	}

}
