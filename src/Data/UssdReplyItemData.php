<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class UssdReplyItemData
{
	public function __construct(
		public ?int $port = null,
		public ?string $text = null
	) {
	}
}
