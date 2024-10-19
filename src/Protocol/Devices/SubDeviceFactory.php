<?php declare(strict_types = 1);

/**
 * SubDeviceFactory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 * @since          1.0.0
 *
 * @date           04.10.24
 */

namespace FastyBird\Connector\NsPanel\Protocol\Devices;

use FastyBird\Connector\NsPanel\Documents;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Helpers;
use FastyBird\Connector\NsPanel\Mapping;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use TypeError;
use ValueError;
use function array_map;
use function array_merge;
use function assert;

/**
 * NS panel sub-device factory
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class SubDeviceFactory implements DeviceFactory
{

	public function __construct(
		private readonly Mapping\Builder $mappingBuilder,
		private readonly Helpers\Devices\SubDevice $deviceHelper,
	)
	{
	}

	/**
	 * @throws DevicesExceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\Runtime
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function create(
		Documents\Connectors\Connector $connector,
		Documents\Devices\Gateway $gateway,
		Documents\Devices\Device $device,
		Mapping\Categories\Category $categoryMetadata,
	): SubDevice
	{
		assert($device instanceof Documents\Devices\SubDevice);

		return new SubDevice(
			$device->getId(),
			$device->getIdentifier(),
			$gateway->getId(),
			$connector->getId(),
			$this->deviceHelper->getDisplayCategory($device),
			$device->getName() ?? $device->getIdentifier(),
			$this->deviceHelper->getManufacturer($device),
			$this->deviceHelper->getModel($device),
			$this->deviceHelper->getFirmwareVersion($device),
			array_merge(
				...array_map(function (Types\Group $type): array {
					$group = $this->mappingBuilder->getCapabilitiesMapping()->getGroup($type);

					return array_map(
						static fn (Mapping\Capabilities\Capability $capabilityMeta): Types\Capability => $capabilityMeta->getCapability(),
						$group->getCapabilities(),
					);
				}, $categoryMetadata->getRequiredCapabilitiesGroups()),
			),
			array_merge(
				...array_map(function (Types\Group $type): array {
					$group = $this->mappingBuilder->getCapabilitiesMapping()->getGroup($type);

					return array_map(
						static fn (Mapping\Capabilities\Capability $capabilityMeta): Types\Capability => $capabilityMeta->getCapability(),
						$group->getCapabilities(),
					);
				}, $categoryMetadata->getOptionalCapabilitiesGroups()),
			),
		);
	}

	public function getEntityClass(): string
	{
		return Entities\Devices\SubDevice::class;
	}

}
