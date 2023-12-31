<?php declare(strict_types = 1);

/**
 * GetGatewayAccessToken.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           10.07.23
 */

namespace FastyBird\Connector\NsPanel\Entities\API\Response;

use FastyBird\Connector\NsPanel\Entities;
use Orisai\ObjectMapper;
use stdClass;

/**
 * NS Panel acquire access token response definition
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class GetGatewayAccessToken implements Entities\API\Entity
{

	public function __construct(
		#[ObjectMapper\Rules\IntValue(unsigned: true)]
		private readonly int $error,
		#[ObjectMapper\Rules\MappedObjectValue(GetGatewayAccessTokenData::class)]
		private readonly GetGatewayAccessTokenData $data,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		private readonly string $message,
	)
	{
	}

	public function getError(): int
	{
		return $this->error;
	}

	public function getData(): GetGatewayAccessTokenData
	{
		return $this->data;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'error' => $this->getError(),
			'data' => $this->getData()->toArray(),
			'message' => $this->getMessage(),
		];
	}

	public function toJson(): object
	{
		$json = new stdClass();
		$json->error = $this->getError();
		$json->data = $this->getData()->toJson();
		$json->message = $this->getMessage();

		return $json;
	}

}
