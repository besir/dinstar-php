<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class QueryIncomingSmsData
{

	public function __construct(public ?IncomingSmsItemCollection $sms = null, public ?int $read = null, public ?int $unread = null)
	{}
}
