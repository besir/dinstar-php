<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Collection\CdrItemCollection;
use Besir\Dinstar\Data\IData;

class GetCdrResponse extends DinstarApiResponse
{

	private ?CdrItemCollection $data;

	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		?CdrItemCollection $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
		parent::__construct($success, $httpCode, $errorCode, null, $rawResponse, $errorMessage, $gatewaySn);
		$this->data = $data;
	}

	public function getData(): ?CdrItemCollection
	{
		return $this->data;
	}
}
