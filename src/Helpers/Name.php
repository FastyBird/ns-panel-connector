<?php declare(strict_types = 1);

/**
 * Name.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Helpers
 * @since          1.0.0
 *
 * @date           12.07.23
 */

namespace FastyBird\Connector\NsPanel\Helpers;

use FastyBird\Connector\NsPanel\Exceptions;
use FastyBird\Connector\NsPanel\Types;
use TypeError;
use ValueError;

/**
 * Useful name helpers
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Name
{

	public static function convertCapabilityToChannel(Types\Capability $type, string|int|null $name = null): string
	{
		$identifier = $type->value;

		if ($name !== null) {
			$identifier .= '_' . $name;
		}

		return $identifier;
	}

	public static function convertAttributeToProperty(Types\Attribute $type): string
	{
		return $type->value;
	}

	/**
	 * @return ($throw is true ? Types\Attribute : Types\Attribute|null)
	 *
	 * @throws Exceptions\InvalidArgument
	 */
	public static function convertPropertyToAttribute(string $identifier, bool $throw = true): Types\Attribute|null
	{
		try {
			return Types\Attribute::from($identifier);
		} catch (TypeError | ValueError) {
			if ($throw) {
				throw new Exceptions\InvalidArgument('Provided property identifier is not valid attribute');
			}
		}

		return null;
	}

	public static function convertConfigurationToProperty(Types\Configuration $type): string
	{
		return $type->value;
	}

	/**
	 * @return ($throw is true ? Types\Configuration : Types\Configuration|null)
	 *
	 * @throws Exceptions\InvalidArgument
	 */
	public static function convertPropertyToConfiguration(
		string $identifier,
		bool $throw = true,
	): Types\Configuration|null
	{
		try {
			return Types\Configuration::from($identifier);
		} catch (TypeError | ValueError) {
			if ($throw) {
				throw new Exceptions\InvalidArgument('Provided property identifier is not valid configuration');
			}
		}

		return null;
	}

}
