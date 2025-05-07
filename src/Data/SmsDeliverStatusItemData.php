<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class SmsDeliverStatusItemData
{
	public function __construct(
		public ?int $port = null,
		public ?string $number = null,
		public ?string $time = null,
		public ?int $refId = null,
		public ?int $statusCode = null,
		public ?string $imsi = null
	) {
	}
}
