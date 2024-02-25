<?php declare(strict_types = 1);

/**
 * GetGatewayInfo.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     API
 * @since          1.0.0
 *
 * @date           10.07.23
 */

namespace FastyBird\Connector\NsPanel\API\Messages\Response;

use FastyBird\Connector\NsPanel\API;
use Orisai\ObjectMapper;
use stdClass;

/**
 * NS Panel report its description response definition
 *
 * @package        FastyBird:NsPanelConnector!
 * @subpackage     API
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final readonly class GetGatewayInfo implements API\Messages\Message
{

	public function __construct(
		#[ObjectMapper\Rules\IntValue(unsigned: true)]
		private int $error,
		#[ObjectMapper\Rules\MappedObjectValue(GetGatewayInfoData::class)]
		private GetGatewayInfoData $data,
		#[ObjectMapper\Rules\StringValue(notEmpty: true)]
		private string $message,
	)
	{
	}

	public function getError(): int
	{
		return $this->error;
	}

	public function getData(): GetGatewayInfoData
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
			'data' => $this->getData(),
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
