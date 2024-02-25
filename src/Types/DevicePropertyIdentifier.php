<?php declare(strict_types = 1);

/**
 * DevicePropertyIdentifier.php
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

namespace FastyBird\Connector\NsPanel\Types;

use FastyBird\Module\Devices\Types as DevicesTypes;

/**
 * Device property identifier types
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
enum DevicePropertyIdentifier: string
{

	case STATE = DevicesTypes\DevicePropertyIdentifier::STATE->value;

	case IP_ADDRESS = DevicesTypes\DevicePropertyIdentifier::IP_ADDRESS->value;

	case DOMAIN = DevicesTypes\DevicePropertyIdentifier::DOMAIN->value;

	case MANUFACTURER = DevicesTypes\DevicePropertyIdentifier::HARDWARE_MANUFACTURER->value;

	case MODEL = DevicesTypes\DevicePropertyIdentifier::HARDWARE_MODEL->value;

	case MAC_ADDRESS = DevicesTypes\DevicePropertyIdentifier::HARDWARE_MAC_ADDRESS->value;

	case FIRMWARE_VERSION = DevicesTypes\DevicePropertyIdentifier::FIRMWARE_VERSION->value;

	case ACCESS_TOKEN = 'access_token';

	case CATEGORY = 'category';

	case GATEWAY_IDENTIFIER = 'gateway_identifier';

	case STATE_READING_DELAY = DevicesTypes\DevicePropertyIdentifier::STATE_READING_DELAY->value;

}
