<?php declare(strict_types = 1);

/**
 * SubDevice.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           11.07.23
 */

namespace FastyBird\Connector\NsPanel\Entities\Devices;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\Connector\NsPanel\Entities;
use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Application\Entities\Mapping as ApplicationMapping;
use FastyBird\Library\Metadata\Exceptions as MetadataExceptions;
use FastyBird\Module\Devices\Entities as DevicesEntities;
use Nette\Utils;
use Ramsey\Uuid;
use TypeError;
use ValueError;
use function count;
use function is_string;

#[ORM\Entity]
#[ApplicationMapping\DiscriminatorEntry(name: self::TYPE)]
class SubDevice extends Entities\Devices\Device
{

	public const TYPE = 'ns-panel-connector-sub-device';

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function __construct(
		string $identifier,
		Gateway $parent,
		Entities\Connectors\Connector $connector,
		string|null $name = null,
		Uuid\UuidInterface|null $id = null,
	)
	{
		parent::__construct($identifier, $connector, $name, $id);

		$this->setParents([$parent]);
	}

	public static function getType(): string
	{
		return self::TYPE;
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function getGateway(): Gateway
	{
		foreach ($this->parents->toArray() as $parent) {
			if ($parent instanceof Gateway) {
				return $parent;
			}
		}

		throw new Exceptions\InvalidState('Sub-device have to have parent gateway defined');
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function setParents(array|Utils\ArrayHash $parents): void
	{
		if (count($parents) !== 1 || !$parents[0] instanceof Gateway) {
			throw new Exceptions\InvalidState('Sub-device could have only one parent and it have to be gateway');
		}

		parent::setParents($parents);
	}

	/**
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function getDisplayCategory(): Types\Category
	{
		$property = $this->properties
			->filter(
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				static fn (DevicesEntities\Devices\Properties\Property $property): bool => $property->getIdentifier() === Types\DevicePropertyIdentifier::CATEGORY->value
			)
			->first();

		if (
			$property instanceof DevicesEntities\Devices\Properties\Variable
			&& is_string($property->getValue())
			&& Types\Category::tryFrom($property->getValue()) !== null
		) {
			return Types\Category::from($property->getValue());
		}

		return Types\Category::UNKNOWN;
	}

	/**
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function getManufacturer(): string
	{
		$property = $this->properties
			->filter(
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				static fn (DevicesEntities\Devices\Properties\Property $property): bool => $property->getIdentifier() === Types\DevicePropertyIdentifier::MANUFACTURER->value
			)
			->first();

		if (
			$property instanceof DevicesEntities\Devices\Properties\Variable
			&& is_string($property->getValue())
		) {
			return $property->getValue();
		}

		return 'N/A';
	}

	/**
	 * @throws MetadataExceptions\InvalidArgument
	 * @throws MetadataExceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function getModel(): string
	{
		$property = $this->properties
			->filter(
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				static fn (DevicesEntities\Devices\Properties\Property $property): bool => $property->getIdentifier() === Types\DevicePropertyIdentifier::MODEL->value
			)
			->first();

		if (
			$property instanceof DevicesEntities\Devices\Properties\Variable
			&& is_string($property->getValue())
		) {
			return $property->getValue();
		}

		return 'N/A';
	}

}
