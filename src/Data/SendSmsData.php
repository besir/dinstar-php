<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class SendSmsData
{
	public function __construct(
		public ?int $smsInQueue = null,
		public ?int $taskId = null
	) {
	}
}
