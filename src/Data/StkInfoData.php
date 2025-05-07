<?php

declare(strict_types=1);

namespace Besir\Dinstar\Data;

class StkInfoData
{
	/** @var array<array{item_id: int, item_string: string}>|null */
	public ?array $item = null; // Internal structure {item_id, item_string} is from API

	public function __construct(
		public ?string $title = null,
		public ?string $text = null,
		public ?int $inputType = null,
		?array $itemData = null, // Raw item data from API
		public ?int $frameId = null
	) {
		if ($itemData !== null) {
			// Preserving API's snake_case for internal structure of 'item'
			$this->item = array_map(fn($i) => ['item_id' => $i['item_id'], 'item_string' => $i['item_string']], $itemData);
		}
	}
}
