<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Data\IData;

/**
 * Base Data Transfer Object for Dinstar API responses.
 */
class DinstarApiResponse implements IDinstarResponse
{

	/**
	 * Constructor for DinstarApiResponse using promoted properties (PHP 8.0+).
	 *
	 * @param bool $success Overall success status of the API operation.
	 * @param int $httpCode HTTP status code received.
	 * @param int|null $errorCode Dinstar specific error code from the response payload.
	 * @param IData|null $data Extracted data payload (generic type for base class).
	 * @param string|null $rawResponse Raw response body (usually only on error).
	 * @param string|null $errorMessage Technical or API error message.
	 * @param string|null $gatewaySn Gateway serial number from the response payload.
	 */
	public function __construct(
		public bool $success,
		public int $httpCode,
		public ?int $errorCode = null,
		private readonly mixed $data = null,
		public ?string $rawResponse = null,
		public ?string $errorMessage = null,
		public ?string $gatewaySn = null
	) {
	}

	public function isSuccessful(): bool
	{
		return $this->success;
	}

	public function getHttpCode(): int
	{
		return $this->httpCode;
	}

	public function getErrorCode(): ?int
	{
		return $this->errorCode;
	}

	public function getData(): mixed
	{
		return $this->data;
	}

	public function getRawResponse(): ?string
	{
		return $this->rawResponse;
	}

	public function getErrorMessage(): ?string
	{
		return $this->errorMessage;
	}

	public function getGatewaySn(): ?string
	{
		return $this->gatewaySn;
	}
}
