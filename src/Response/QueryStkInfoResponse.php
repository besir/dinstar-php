<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Data\StkInfoData;

class QueryStkInfoResponse extends DinstarApiResponse
{
	private ?StkInfoData $data;

	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		?StkInfoData $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
		parent::__construct($success, $httpCode, $errorCode, null, $rawResponse, $errorMessage, $gatewaySn);
		$this->data = $data;
	}

	public function getData(): ?StkInfoData
	{
		return $this->data;
	}
}
