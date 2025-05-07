<?php

declare(strict_types=1);

namespace Besir\Dinstar\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class IncomingSmsItemCollection implements IteratorAggregate, Countable
{
	/** @var IncomingSmsItemData[] */
	public readonly array $items;

	/** @param IncomingSmsItemData[] $items */
	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			if (!($item instanceof IncomingSmsItemData)) {
				throw new \InvalidArgumentException('All items must be instances of ' . IncomingSmsItemData::class);
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
