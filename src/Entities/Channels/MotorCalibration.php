<?php declare(strict_types = 1);

/**
 * MotorCalibration.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           02.10.24
 */

namespace FastyBird\Connector\NsPanel\Entities\Channels;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Application\Entities\Mapping as ApplicationMapping;
use Ramsey\Uuid;

#[ORM\Entity]
#[ApplicationMapping\DiscriminatorEntry(name: self::TYPE)]
class MotorCalibration extends Channel
{

	public const TYPE = 'ns-panel-connector-motor-calibration';

	public function __construct(
		Entities\Devices\Device $device,
		string|null $name = null,
		Uuid\UuidInterface|null $id = null,
	)
	{
		parent::__construct($device, Types\Capability::MOTOR_CALIBRATION->value, $name, $id);
	}

	public static function getType(): string
	{
		return self::TYPE;
	}

	public function getCapability(): Types\Capability
	{
		return Types\Capability::MOTOR_CALIBRATION;
	}

}
