<?php declare(strict_types = 1);

/**
 * Press.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 * @since          1.0.0
 *
 * @date           04.10.24
 */

namespace FastyBird\Connector\NsPanel\Protocol\Attributes;

use FastyBird\Connector\NsPanel\Protocol;
use FastyBird\Connector\NsPanel\Types;
use FastyBird\Library\Metadata\Types as MetadataTypes;
use Ramsey\Uuid;

/**
 * Press attribute
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Protocol
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Press extends Attribute
{

	public function __construct(
		Uuid\UuidInterface $id,
		Protocol\Capabilities\Capability $capability,
	)
	{
		parent::__construct(
			$id,
			Types\Attribute::PRESS,
			MetadataTypes\DataType::ENUM,
			$capability,
			[
				Types\Payloads\Press::SINGLE_PRESS->value,
				Types\Payloads\Press::DOUBLE_PRESS->value,
				Types\Payloads\Press::LONG_PRESS->value,
			],
		);
	}

}
