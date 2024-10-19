<?php declare(strict_types = 1);

/**
 * Attribute.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Types
 * @since          1.0.0
 *
 * @date           22.07.23
 */

namespace FastyBird\Connector\NsPanel\Types;

/**
 * Capability attribute types
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
enum Attribute: string
{

	case POWER_STATE = 'powerState';

	case TOGGLE_STATE = 'toggleState';

	case BRIGHTNESS = 'brightness';

	case COLOR_TEMPERATURE = 'colorTemperature';

	case COLOR_RED = 'red';

	case COLOR_GREEN = 'green';

	case COLOR_BLUE = 'blue';

	case PERCENTAGE = 'percentage';

	case MOTOR_CONTROL = 'motorControl';

	case MOTOR_REVERSE = 'motorReverse';

	case MOTOR_CALIBRATION = 'motorClb';

	case STARTUP = 'startup';

	case DETECTED = 'detected';

	case HUMIDITY = 'humidity';

	case TEMPERATURE = 'temperature';

	case BATTERY = 'battery';

	case PRESS = 'press';

	case RSSI = 'rssi';

	case LEVEL = 'level';

	case THERMOSTAT_MODE = 'thermostatMode';

	case ADAPTIVE_RECOVERY_STATUS = 'adaptiveRecoveryStatus';

	case MODE = 'mode';

	case TARGET_SET_POINT = 'targetSetpoint';

	case FAULT = 'fault';

}
