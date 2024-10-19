<?php declare(strict_types = 1);

/**
 * Battery.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Hydrators
 * @since          1.0.0
 *
 * @date           10.10.24
 */

namespace FastyBird\Connector\NsPanel\Hydrators\Channels;

use FastyBird\Connector\NsPanel\Entities;

/**
 * NS Panel battery capability channel entity hydrator
 *
 * @extends Channel<Entities\Channels\Battery>
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Hydrators
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Battery extends Channel
{

	public function getEntityName(): string
	{
		return Entities\Channels\Battery::class;
	}

}
