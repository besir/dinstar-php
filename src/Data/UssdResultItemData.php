<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class UssdResultItemData
{
	public function __construct(
		public ?int $port = null,
		public ?int $status = null
	) {
	}
}
