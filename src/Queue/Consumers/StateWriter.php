<?php declare(strict_types = 1);

/**
 * Channel.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           16.07.23
 */

namespace FastyBird\Connector\NsPanel\Queue\Consumers;

use DateTimeInterface;
use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Helpers;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Documents as MetadataDocuments;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use FastyBird\Library\Metadata\Utilities as MetadataUtilities;
use FastyBird\Module\Devices\Exceptions as DevicesExceptions;
use FastyBird\Module\Devices\Models as DevicesModels;
use FastyBird\Module\Devices\Queries as DevicesQueries;
use FastyBird\Module\Devices\Utilities as DevicesUtilities;
use function boolval;
use function floatval;
use function intval;
use function is_bool;
use function is_float;
use function is_int;
use function strval;

/**
 * Third-party device & sub-device property to state mapper
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read Helpers\Channel $channelHelper
 * @property-read DevicesModels\Configuration\Channels\Properties\Repository $channelsPropertiesConfigurationRepository
 * @property-read DevicesUtilities\ChannelPropertiesStates $channelPropertiesStatesManager
 */
trait StateWriter
{

	/**
	 * @return array<mixed>|null
	 *
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function mapChannelToState(
		MetadataDocuments\DevicesModule\Channel $channel,
	): array|null
	{
		switch ($this->channelHelper->getCapability($channel)->getValue()) {
			case Types\Capability::POWER:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::POWER_STATE));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || !Types\PowerPayload::isValidValue($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::POWER_STATE => $value,
					],
				];
			case Types\Capability::TOGGLE:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::TOGGLE_STATE));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || !Types\TogglePayload::isValidValue($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						$channel->getIdentifier() => [
							Types\Protocol::TOGGLE_STATE => $value,
						],
					],
				];
			case Types\Capability::BRIGHTNESS:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::BRIGHTNESS));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::BRIGHTNESS => intval($value),
					],
				];
			case Types\Capability::COLOR_TEMPERATURE:
				$property = $this->findProtocolProperty(
					$channel,
					Types\Protocol::get(Types\Protocol::COLOR_TEMPERATURE),
				);

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::COLOR_TEMPERATURE => intval($value),
					],
				];
			case Types\Capability::COLOR_RGB:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::COLOR_RED));

				if ($property === null) {
					return null;
				}

				$red = $this->getPropertyValue($property);

				if (!is_int($red)) {
					$red = $property->getInvalid();
				}

				if ($red === null) {
					return null;
				}

				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::COLOR_GREEN));

				if ($property === null) {
					return null;
				}

				$green = $this->getPropertyValue($property);

				if (!is_int($green)) {
					$green = $property->getInvalid();
				}

				if ($green === null) {
					return null;
				}

				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::COLOR_BLUE));

				if ($property === null) {
					return null;
				}

				$blue = $this->getPropertyValue($property);

				if (!is_int($blue)) {
					$blue = $property->getInvalid();
				}

				if ($blue === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::COLOR_RED => intval($red),
						Types\Protocol::COLOR_GREEN => intval($green),
						Types\Protocol::COLOR_BLUE => intval($blue),
					],
				];
			case Types\Capability::PERCENTAGE:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::PERCENTAGE));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::PERCENTAGE => intval($value),
					],
				];
			case Types\Capability::MOTOR_CONTROL:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::MOTOR_CONTROL));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || !Types\MotorControlPayload::isValidValue($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::MOTOR_CONTROL => $value,
					],
				];
			case Types\Capability::MOTOR_REVERSE:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::MOTOR_REVERSE));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_bool($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::MOTOR_REVERSE => boolval($value),
					],
				];
			case Types\Capability::MOTOR_CALIBRATION:
				$property = $this->findProtocolProperty(
					$channel,
					Types\Protocol::get(Types\Protocol::MOTOR_CALIBRATION),
				);

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || !Types\MotorCalibrationPayload::isValidValue($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::MOTOR_CALIBRATION => $value,
					],
				];
			case Types\Capability::STARTUP:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::STARTUP));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || !Types\StartupPayload::isValidValue($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::STARTUP => $value,
					],
				];
			case Types\Capability::CAMERA_STREAM:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::STREAM_URL));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::CONFIGURATION => [
							Types\Protocol::STREAM_URL => strval($value),
						],
					],
				];
			case Types\Capability::DETECT:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::DETECT));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_bool($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::DETECT => boolval($value),
					],
				];
			case Types\Capability::HUMIDITY:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::HUMIDITY));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::HUMIDITY => intval($value),
					],
				];
			case Types\Capability::TEMPERATURE:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::TEMPERATURE));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_float($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::TEMPERATURE => floatval($value),
					],
				];
			case Types\Capability::BATTERY:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::BATTERY));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::BATTERY => intval($value),
					],
				];
			case Types\Capability::PRESS:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::PRESS));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if ($value === null || Types\PressPayload::isValidValue($value)) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::PRESS => $value,
					],
				];
			case Types\Capability::RSSI:
				$property = $this->findProtocolProperty($channel, Types\Protocol::get(Types\Protocol::RSSI));

				if ($property === null) {
					return null;
				}

				$value = $this->getPropertyValue($property);

				if (!is_int($value)) {
					$value = $property->getInvalid();
				}

				if ($value === null) {
					return null;
				}

				return [
					$this->channelHelper->getCapability($channel)->getValue() => [
						Types\Protocol::RSSI => intval($value),
					],
				];
		}

		throw new Exceptions\InvalidArgument('Provided property type is not supported');
	}

	/**
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws Exceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	private function getPropertyValue(
		MetadataDocuments\DevicesModule\ChannelProperty $property,
	): string|int|float|bool|null
	{
		if (
			$property instanceof MetadataDocuments\DevicesModule\ChannelDynamicProperty
			|| $property instanceof MetadataDocuments\DevicesModule\ChannelMappedProperty
		) {
			$actualValue = $this->getActualValue($property);
			$expectedValue = $this->getExpectedValue($property);

			$value = $expectedValue ?? $actualValue;
		} elseif ($property instanceof MetadataDocuments\DevicesModule\ChannelVariableProperty) {
			$value = $property->getValue();
		} else {
			throw new Exceptions\InvalidArgument('Provided property is not valid');
		}

		return MetadataUtilities\ValueHelper::transformValueToDevice(
			$property->getDataType(),
			$property->getFormat(),
			$value,
		);
	}

	/**
	 * @throws DevicesExceptions\InvalidState
	 */
	private function findProtocolProperty(
		MetadataDocuments\DevicesModule\Channel $channel,
		Types\Protocol $protocol,
	): MetadataDocuments\DevicesModule\ChannelProperty|null
	{
		$findChannelPropertyQuery = new DevicesQueries\Configuration\FindChannelProperties();
		$findChannelPropertyQuery->forChannel($channel);
		$findChannelPropertyQuery->byIdentifier(Helpers\Name::convertProtocolToProperty($protocol));

		return $this->channelsPropertiesConfigurationRepository->findOneBy($findChannelPropertyQuery);
	}

	/**
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function getActualValue(
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty $property,
	): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayload|MetadataTypes\SwitchPayload|MetadataTypes\CoverPayload|null
	{
		return $this->channelPropertiesStatesManager->readValue($property)?->getActualValue();
	}

	/**
	 * @throws DevicesExceptions\InvalidArgument
	 * @throws DevicesExceptions\InvalidState
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws MetadataExceptions\MalformedInput
	 */
	public function getExpectedValue(
		MetadataDocuments\DevicesModule\ChannelDynamicProperty|MetadataDocuments\DevicesModule\ChannelMappedProperty $property,
	): bool|float|int|string|DateTimeInterface|MetadataTypes\ButtonPayload|MetadataTypes\SwitchPayload|MetadataTypes\CoverPayload|null
	{
		return $this->channelPropertiesStatesManager->readValue($property)?->getExpectedValue();
	}

}
