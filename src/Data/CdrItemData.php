<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class CdrItemData implements IData
{
	public function __construct(
		public ?int $port = null,
		public ?string $startDate = null,
		public ?string $answerDate = null,
		public ?int $duration = null,
		public ?string $sourceNumber = null,
		public ?string $destinationNumber = null,
		public ?string $direction = null,
		public ?string $ip = null,
		public ?string $codec = null,
		public ?string $hangup = null,
		public ?int $gsmCode = null,
		public ?string $bcch = null // API uses 'bcch' or 'bech'
	) {
	}
}
