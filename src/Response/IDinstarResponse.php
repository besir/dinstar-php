<?php

declare(strict_types=1);

namespace Besir\Dinstar\Response;

use Besir\Dinstar\Data\IData;

/**
 * Interface for Dinstar API responses.
 */
interface IDinstarResponse
{
	public function isSuccessful(): bool;
	public function getHttpCode(): int;
	public function getErrorCode(): ?int;
	public function getData(): mixed;
	public function getRawResponse(): ?string;
	public function getErrorMessage(): ?string;
	public function getGatewaySn(): ?string;
}
