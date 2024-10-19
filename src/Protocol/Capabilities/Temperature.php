<?php declare(strict_types = 1);

/**
 * Temperature.php
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

namespace FastyBird\Connector\NsPanel\Protocol\Capabilities;

use FastyBird\Connector\NsPanel\Protocol;
use FastyBird\Connector\NsPanel\Types;
use Ramsey\Uuid;

/**
 * NS Panel device temperature capability
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Temperature extends Capability
{

	/**
	 * @param array<int, Types\Configuration> $requiredConfigurations
	 * @param array<int, Types\Configuration> $optionalConfigurations
	 * @param array<int, Types\Attribute> $requiredAttributes
	 * @param array<int, Types\Attribute> $optionalAttributes
	 */
	public function __construct(
		Uuid\UuidInterface $id,
		Types\Permission $permission,
		Protocol\Devices\Device $device,
		string|null $name = null,
		array $requiredConfigurations = [],
		array $optionalConfigurations = [],
		array $requiredAttributes = [],
		array $optionalAttributes = [],
	)
	{
		parent::__construct(
			$id,
			Types\Capability::TEMPERATURE,
			$permission,
			$device,
			$name,
			$requiredConfigurations,
			$optionalConfigurations,
			$requiredAttributes,
			$optionalAttributes,
		);
	}

}
