<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class PortInfoItemData
{
	public function __construct(
		public ?int $port = null,
		public ?string $type = null,
		public ?string $imei = null,
		public ?string $imsi = null,
		public ?string $iccid = null,
		public ?string $number = null,
		public ?string $reg = null,
		public ?int $slot = null,
		public ?string $callState = null,
		public ?int $signal = null,
		public ?string $gprs = null,
		public ?string $remainCredit = null,
		public ?string $remainMonthlyCredit = null,
		public ?string $remainDailyCredit = null,
		public ?string $remainDailyCallTime = null,
		public ?string $remainHourlyCallTime = null,
		public ?string $remainDailyConnect = null,
		public mixed $callForwarding = null // API uses 'CallForwarding' or 'CallForward'
	) {
	}
}
