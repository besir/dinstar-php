<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

class QuerySmsInQueueResponse extends DinstarApiResponse
{
	private ?int $data; // 'in_queue' value

	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		?int $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
		parent::__construct($success, $httpCode, $errorCode, null, $rawResponse, $errorMessage, $gatewaySn);
		$this->data = $data;
	}

	/**
	 * @return int|null The SMS in queue data or null if not set.
	 */
	public function getData(): ?int
	{
		return $this->data;
	}
}
