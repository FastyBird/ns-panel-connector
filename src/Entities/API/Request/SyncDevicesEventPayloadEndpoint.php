<?php declare(strict_types = 1);

/**
 * SyncDevicesEventPayload.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           09.07.23
 */

namespace FastyBird\Connector\NsPanel\Entities\API\Request;

use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Bootstrap\ObjectMapper as BootstrapObjectMapper;
use Orisai\ObjectMapper;
use Ramsey\Uuid;
use stdClass;
use function array_map;
use function is_array;

/**
 * Synchronise third-party devices with NS Panel event payload endpoint request definition
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class SyncDevicesEventPayloadEndpoint implements Entities\API\Entity
{

	/**
	 * @param array<Entities\API\Capability> $capabilities
	 * @param array<string, string|array<string, string>> $tags
	 */
	public function __construct(
		#[BootstrapObjectMapper\Rules\UuidValue()]
		#[ObjectMapper\Modifiers\FieldName('third_serial_number')]
		private readonly Uuid\UuidInterface $thirdSerialNumber,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		private readonly string $name,
		#[BootstrapObjectMapper\Rules\ConsistenceEnumValue(class: Types\Category::class)]
		#[ObjectMapper\Modifiers\FieldName('display_category')]
		private readonly Types\Category $displayCategory,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\MappedObjectValue(Entities\API\Capability::class),
		)]
		private readonly array $capabilities,
		#[ObjectMapper\Rules\MappedObjectValue(Entities\API\State::class)]
		private readonly Entities\API\State $state,
		#[ObjectMapper\Rules\ArrayOf(
			item: new ObjectMapper\Rules\AnyOf([
				new ObjectMapper\Rules\StringValue(),
				new ObjectMapper\Rules\ArrayOf(
					item: new ObjectMapper\Rules\StringValue(),
					key: new ObjectMapper\Rules\AnyOf([
						new ObjectMapper\Rules\StringValue(),
						new ObjectMapper\Rules\IntValue(),
					]),
				),
			]),
			key: new ObjectMapper\Rules\StringValue(),
		)]
		private readonly array $tags,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		private readonly string $manufacturer,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		private readonly string $model,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		#[ObjectMapper\Modifiers\FieldName('firmware_version')]
		private readonly string $firmwareVersion,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		#[ObjectMapper\Modifiers\FieldName('service_address')]
		private readonly string $serviceAddress,
		#[ObjectMapper\Rules\BoolValue()]
		private readonly bool $online = false,
	)
	{
	}

	public function getThirdSerialNumber(): Uuid\UuidInterface
	{
		return $this->thirdSerialNumber;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getDisplayCategory(): Types\Category
	{
		return $this->displayCategory;
	}

	/**
	 * @return array<Entities\API\Capability>
	 */
	public function getCapabilities(): array
	{
		return $this->capabilities;
	}

	/**
	 * @return array<Entities\API\States\State>
	 */
	public function getState(): array
	{
		return $this->state->getStates();
	}

	/**
	 * @return array<string, string|array<string, string>>
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	public function getManufacturer(): string
	{
		return $this->manufacturer;
	}

	public function getModel(): string
	{
		return $this->model;
	}

	public function getFirmwareVersion(): string
	{
		return $this->firmwareVersion;
	}

	public function getServiceAddress(): string
	{
		return $this->serviceAddress;
	}

	public function isOnline(): bool
	{
		return $this->online;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'third_serial_number' => $this->getThirdSerialNumber()->toString(),
			'name' => $this->getName(),
			'display_category' => $this->getDisplayCategory()->getValue(),
			'capabilities' => array_map(
				static fn (Entities\API\Capability $capability): array => $capability->toArray(),
				$this->getCapabilities(),
			),
			'state' => $this->state->toArray(),
			'tags' => $this->getTags(),
			'manufacturer' => $this->getManufacturer(),
			'model' => $this->getModel(),
			'firmware_version' => $this->getFirmwareVersion(),
			'service_address' => $this->getServiceAddress(),
			'online' => $this->isOnline(),
		];
	}

	public function toJson(): object
	{
		$tags = new stdClass();

		foreach ($this->getTags() as $name => $value) {
			if (is_array($value)) {
				$tags->{$name} = new stdClass();

				foreach ($value as $subName => $subValue) {
					$tags->{$name}->{$subName} = $subValue;
				}
			} else {
				$tags->{$name} = $value;
			}
		}

		$json = new stdClass();
		$json->third_serial_number = $this->getThirdSerialNumber();
		$json->name = $this->getName();
		$json->display_category = $this->getDisplayCategory()->getValue();
		$json->capabilities = array_map(
			static fn (Entities\API\Capability $capability): object => $capability->toJson(),
			$this->getCapabilities(),
		);
		$json->state = $this->state->toJson();
		$json->tags = $tags;
		$json->manufacturer = $this->getManufacturer();
		$json->model = $this->getModel();
		$json->firmware_version = $this->getFirmwareVersion();
		$json->service_address = $this->getServiceAddress();
		$json->online = $this->isOnline();

		return $json;
	}

}
