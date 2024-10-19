<?php declare(strict_types = 1);

/**
 * Brightness.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     API
 * @since          1.0.0
 *
 * @date           09.07.23
 */

namespace FastyBird\Connector\NsPanel\API\Messages\States;

use FastyBird\Connector\NsPanel\Types;
use Orisai\ObjectMapper;
use stdClass;

/**
 * Brightness control capability state
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     API
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final readonly class Brightness implements State
{

	public function __construct(
		#[ObjectMapper\Rules\IntValue(min: 0, max: 100, unsigned: true)]
		#[ObjectMapper\Modifiers\FieldName(Types\Attribute::BRIGHTNESS->value)]
		private int $brightness,
	)
	{
	}

	public function getType(): Types\Capability
	{
		return Types\Capability::BRIGHTNESS;
	}

	public function getState(): array
	{
		return [
			Types\Attribute::BRIGHTNESS->value => $this->brightness,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'value' => $this->brightness,
		];
	}

	public function toJson(): object
	{
		$json = new stdClass();
		$json->{Types\Attribute::BRIGHTNESS->value} = $this->brightness;

		return $json;
	}

}
