<?php declare(strict_types = 1);

/**
 * TemperatureScale.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 * @since          1.0.0
 *
 * @date           05.10.24
 */

namespace FastyBird\Connector\NsPanel\Protocol\Configurations;

use FastyBird\Connector\NsPanel\Protocol;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Ramsey\Uuid;

/**
 * Thermostat maximal temperature configuration
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class TemperatureScale extends Configuration
{

	public function __construct(
		Uuid\UuidInterface $id,
		Protocol\Capabilities\Capability $capability,
		string $value,
	)
	{
		parent::__construct(
			$id,
			Types\Configuration::TEMPERATURE_SCALE,
			MetadataTypes\DataType::ENUM,
			$capability,
			$value,
			[
				Types\Payloads\TemperatureScale::CELSIUS->value,
				Types\Payloads\TemperatureScale::FAHRENHEIT->value,
			],
		);
	}

}
