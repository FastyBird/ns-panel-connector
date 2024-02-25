<?php declare(strict_types = 1);

/**
 * SubDevice.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Documents
 * @since          1.0.0
 *
 * @date           10.02.24
 */

namespace FastyBird\Connector\NsPanel\Documents\Devices;

use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Library\Metadata\Documents\Mapping as DOC;

#[DOC\Document(entity: Entities\Devices\SubDevice::class)]
#[DOC\DiscriminatorEntry(name: Entities\Devices\SubDevice::TYPE)]
class SubDevice extends Device
{

	public static function getType(): string
	{
		return Entities\Devices\SubDevice::TYPE;
	}

}
