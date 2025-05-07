<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Collection\UssdResultItemCollection;

class SendUssdResponse extends DinstarApiResponse
{
	public ?UssdResultItemCollection $data;

	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		?UssdResultItemCollection $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
		parent::__construct($success, $httpCode, $errorCode, null, $rawResponse, $errorMessage, $gatewaySn);
		$this->data = $data;
	}

	public function getData(): ?UssdResultItemCollection
	{
		return $this->data;
	}
}
