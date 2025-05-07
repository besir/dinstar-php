<?php

declare(strict_types=1);

namespace Besir\Dinstar\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class UssdReplyItemCollection implements IteratorAggregate, Countable
{
	/** @var UssdReplyItemData[] */
	public readonly array $items;

	/** @param UssdReplyItemData[] $items */
	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			if (!($item instanceof UssdReplyItemData)) {
				throw new \InvalidArgumentException('All items must be instances of ' . UssdReplyItemData::class);
			}
		}
		$this->items = $items;
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}
}
