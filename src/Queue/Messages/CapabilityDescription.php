<?php declare(strict_types = 1);

/**
 * CapabilityDescription.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 * @since          1.0.0
 *
 * @date           09.07.23
 */

namespace FastyBird\Connector\NsPanel\Queue\Messages;

use FastyBird\Connector\NsPanel\Types;
use Orisai\ObjectMapper;
use stdClass;

/**
 * Device capability description definition
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Queue
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final readonly class CapabilityDescription implements Message
{

	public function __construct(
		#[ObjectMapper\Rules\BackedEnumValue(class: Types\Capability::class)]
		private Types\Capability $capability,
		#[ObjectMapper\Rules\BackedEnumValue(class: Types\Permission::class)]
		private Types\Permission $permission,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		private string|null $name = null,
	)
	{
	}

	public function getCapability(): Types\Capability
	{
		return $this->capability;
	}

	public function getPermission(): Types\Permission
	{
		return $this->permission;
	}

	public function getName(): string|null
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'capability' => $this->getCapability()->value,
			'permission' => $this->getPermission()->value,
			'name' => $this->getName(),
		];
	}

	public function toJson(): object
	{
		$json = new stdClass();
		$json->capability = $this->getCapability()->value;
		$json->permission = $this->getPermission()->value;

		if ($this->getName() !== null) {
			$json->name = $this->getName();
		}

		return $json;
	}

}
