<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class IncomingSmsItemData
{
	public function __construct(
		public ?int $incomingSmsId = null,
		public ?int $port = null,
		public ?string $number = null,
		public ?string $smsc = null,
		public ?string $timestamp = null,
		public ?string $text = null
	) {
	}
}
