<?php declare(strict_types = 1);

/**
 * PressPayload.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Types
 * @since          1.0.0
 *
 * @date           09.07.23
 */

namespace FastyBird\Connector\NsPanel\Types\Payloads;

/**
 * Button press capability supported payload types
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
enum PressPayload: string implements Payload
{

	case SINGLE_PRESS = 'singlePress';

	case DOUBLE_PRESS = 'doublePress';

	case LONG_PRESS = 'longPress';

}
