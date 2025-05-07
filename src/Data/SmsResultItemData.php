<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class SmsResultItemData
{
	public function __construct(
		public ?int $port = null,
		public ?string $number = null,
		public ?int $userId = null,
		public ?string $time = null,
		public ?string $status = null,
		public ?int $count = null,
		public ?int $succCount = null,
		public ?int $refId = null,
		public ?string $imsi = null
	) {
	}
}
