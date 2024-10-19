<?php declare(strict_types = 1);

/**
 * SupportedDetectionLowerSetPointScaleFactory.php
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
use function is_string;

/**
 * NS panel supported detection lower set point scale capability configuration factory
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class SupportedDetectionLowerSetPointScaleFactory implements ConfigurationFactory
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
	): SupportedDetectionLowerSetPointScale
	{
		assert(is_string($value));

		return new SupportedDetectionLowerSetPointScale(
			$id,
			$capability,
			$value,
		);
	}

	public function getType(): Types\Configuration
	{
		return Types\Configuration::SUPPORTED_LOWER_SET_POINT_VALUE_SCALE;
	}

}
