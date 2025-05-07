<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Collection\UssdReplyItemCollection;

class QueryUssdReplyResponse extends DinstarApiResponse
{
	private  ?UssdReplyItemCollection $data;

	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		?UssdReplyItemCollection $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
		parent::__construct($success, $httpCode, $errorCode, null, $rawResponse, $errorMessage, $gatewaySn);
		$this->data = $data;
	}

	public function getData(): ?UssdReplyItemCollection
	{
		return $this->data;
	}
}
