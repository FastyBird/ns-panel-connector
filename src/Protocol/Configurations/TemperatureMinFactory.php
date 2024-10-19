<?php declare(strict_types = 1);

/**
 * TemperatureMinFactory.php
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

namespace FastyBird\Connector\NsPanel\Protocol\Configurations;

use FastyBird\Connector\NsPanel\Protocol;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Ramsey\Uuid;
use function assert;
use function floatval;
use function is_float;
use function is_int;
use function is_numeric;

/**
 * NS panel temperature min capability configuration factory
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class TemperatureMinFactory implements ConfigurationFactory
{

	public function create(
		Uuid\UuidInterface $id,
		Types\Configuration $type,
		MetadataTypes\DataType $dataType,
		Protocol\Capabilities\Capability $capability,
		float|int|bool|string|array|null $value,
		array|null $validValues = [],
		int|null $maxLength = null,
		float|null $minValue = null,
		float|null $maxValue = null,
		float|null $minStep = null,
		string|null $unit = null,
	): TemperatureMin
	{
		assert(is_int($value) || is_float($value));
		assert(is_numeric($minValue));
		assert(is_numeric($maxValue));

		return new TemperatureMin(
			$id,
			$capability,
			$value,
			floatval($minValue),
			floatval($maxValue),
		);
	}

	public function getType(): Types\Configuration
	{
		return Types\Configuration::TEMPERATURE_MIN;
	}

}
