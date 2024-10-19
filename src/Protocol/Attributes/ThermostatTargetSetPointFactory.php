<?php declare(strict_types = 1);

/**
 * ThermostatTargetSetPointFactory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 * @since          1.0.0
 *
 * @date           16.10.24
 */

namespace FastyBird\Connector\NsPanel\Protocol\Attributes;

use FastyBird\Connector\NsPanel\Protocol;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Ramsey\Uuid;

/**
 * NS panel thermostat target set point attribute factory
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ThermostatTargetSetPointFactory implements AttributeFactory
{

	public function create(
		Uuid\UuidInterface $id,
		Types\Attribute $type,
		MetadataTypes\DataType $dataType,
		Protocol\Capabilities\Capability $capability,
		array|null $validValues = [],
		int|null $maxLength = null,
		float|null $minValue = null,
		float|null $maxValue = null,
		float|null $minStep = null,
		float|int|bool|string|null $defaultValue = null,
		string|null $unit = null,
	): ThermostatTargetSetPoint
	{
		return new ThermostatTargetSetPoint($id, $capability);
	}

	public function getType(): Types\Attribute
	{
		return Types\Attribute::TARGET_SET_POINT;
	}

}
