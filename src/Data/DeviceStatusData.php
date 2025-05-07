<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class DeviceStatusData
{
	public function __construct(
		public ?string $cpuUsed = null,
		public ?string $flashTotal = null,
		public ?string $flashUsed = null,
		public ?string $memoryTotal = null,
		public ?string $memoryCached = null,
		public ?string $memoryBuffers = null,
		public ?string $memoryFree = null,
		public ?string $memoryUsed = null
	) {
	}
}
