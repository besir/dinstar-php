<?php

declare(strict_types=1);

namespace Besir\Dinstar\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class UssdResultItemCollection implements IteratorAggregate, Countable
{
	/** @var UssdResultItemData[] */
	public readonly array $items;

	/** @param UssdResultItemData[] $items */
	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			if (!($item instanceof UssdResultItemData)) {
				throw new \InvalidArgumentException('All items must be instances of ' . UssdResultItemData::class);
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
