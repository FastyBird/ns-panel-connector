<?php declare(strict_types = 1);

/**
 * ThermostatModeDetect.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           16.10.24
 */

namespace FastyBird\Connector\NsPanel\Documents\Channels;

use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Core\Application\Documents as ApplicationDocuments;

#[ApplicationDocuments\Mapping\Document(entity: Entities\Channels\ThermostatModeDetect::class)]
#[ApplicationDocuments\Mapping\DiscriminatorEntry(name: Entities\Channels\ThermostatModeDetect::TYPE)]
class ThermostatModeDetect extends Channel
{

	public static function getType(): string
	{
		return Entities\Channels\ThermostatModeDetect::TYPE;
	}

	public function toDefinition(): array
	{
		return [
			'capability' => Types\Capability::THERMOSTAT_MODE_DETECT->value,
			'permission' => Types\Permission::READ_WRITE->value,
		];
	}

}
