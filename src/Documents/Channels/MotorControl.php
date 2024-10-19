<?php declare(strict_types = 1);

/**
 * MotorControl.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           03.10.24
 */

namespace FastyBird\Connector\NsPanel\Documents\Channels;

use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Documents\Mapping as DOC;

#[DOC\Document(entity: Entities\Channels\MotorControl::class)]
#[DOC\DiscriminatorEntry(name: Entities\Channels\MotorControl::TYPE)]
class MotorControl extends Channel
{

	public static function getType(): string
	{
		return Entities\Channels\MotorControl::TYPE;
	}

	public function toDefinition(): array
	{
		return [
			'capability' => Types\Capability::MOTOR_CONTROL->value,
			'permission' => Types\Permission::READ_WRITE->value,
		];
	}

}
