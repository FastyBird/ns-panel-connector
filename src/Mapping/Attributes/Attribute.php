<?php declare(strict_types = 1);

/**
 * Attribute.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Mapping
 * @since          1.0.0
 *
 * @date           02.10.24
 */

namespace FastyBird\Connector\NsPanel\Mapping\Attributes;

use FastyBird\Connector\NsPanel\Mapping;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Orisai\ObjectMapper;
use function array_filter;

/**
 * Basic attribute interface
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Mapping
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
readonly class Attribute implements Mapping\Mapping
{

	/**
	 * @param array<int, string> $validValues
	 * @param array<int, array<int, string>> $mappedValues
	 */
	public function __construct(
		#[ObjectMapper\Rules\BackedEnumValue(class: Types\Attribute::class)]
		private Types\Attribute $attribute,
		#[ObjectMapper\Rules\BackedEnumValue(class: MetadataTypes\DataType::class)]
		#[ObjectMapper\Modifiers\FieldName('data_type')]
		private MetadataTypes\DataType $dataType,
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		#[ObjectMapper\Modifiers\FieldName('valid_values')]
		private array $validValues = [],
		#[ObjectMapper\Rules\ArrayOf(
			new ObjectMapper\Rules\ArrayOf(
				new ObjectMapper\Rules\StringValue(notEmpty: true),
				new ObjectMapper\Rules\IntValue(unsigned: true),
				3,
				3,
			),
			new ObjectMapper\Rules\IntValue(unsigned: true),
		)]
		#[ObjectMapper\Modifiers\FieldName('mapped_values')]
		private array $mappedValues = [],
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('min_value')]
		private int|float|null $minValue = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('max_value')]
		private int|float|null $maxValue = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('step_value')]
		private int|float|null $stepValue = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('default_value')]
		private int|float|string|null $defaultValue = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\IntValue(),
			new ObjectMapper\Rules\FloatValue(),
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		#[ObjectMapper\Modifiers\FieldName('invalid_value')]
		private int|float|string|null $invalidValue = null,
		#[ObjectMapper\Rules\AnyOf([
			new ObjectMapper\Rules\StringValue(notEmpty: true),
			new ObjectMapper\Rules\NullValue(castEmptyString: true),
		])]
		private string|null $unit = null,
	)
	{
	}

	public function getAttribute(): Types\Attribute
	{
		return $this->attribute;
	}

	public function getDataType(): MetadataTypes\DataType
	{
		return $this->dataType;
	}

	/**
	 * @return array<int, string>
	 */
	public function getValidValues(): array
	{
		return $this->validValues;
	}

	/**
	 * @return array<int, array<int, string>>
	 */
	public function getMappedValues(): array
	{
		return $this->mappedValues;
	}

	public function getMinValue(): float|int|null
	{
		return $this->minValue;
	}

	public function getMaxValue(): float|int|null
	{
		return $this->maxValue;
	}

	public function getStepValue(): float|int|null
	{
		return $this->stepValue;
	}

	public function getDefaultValue(): float|int|string|null
	{
		return $this->defaultValue;
	}

	public function getInvalidValue(): float|int|string|null
	{
		return $this->invalidValue;
	}

	public function getUnit(): string|null
	{
		return $this->unit;
	}

	/**
	 * @return array<string, array<int, array<int, string>|string>|float|int|string|null>
	 */
	public function toArray(): array
	{
		$data = [
			'attribute' => $this->getAttribute()->value,
			'data_type' => $this->getDataType()->value,
			'valid_values' => $this->getValidValues(),
			'mapped_values' => $this->getMappedValues(),
			'min_value' => $this->getMinValue(),
			'max_value' => $this->getMaxValue(),
			'step_value' => $this->getStepValue(),
			'default_value' => $this->getDefaultValue(),
			'invalid_value' => $this->getInvalidValue(),
			'unit' => $this->getUnit(),
		];

		return array_filter($data, static fn ($item): bool => $item !== null && $item !== []);
	}

}
