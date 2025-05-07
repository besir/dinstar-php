<?php

declare(strict_types=1);

namespace Besir\Dinstar\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class PortInfoItemCollection implements IteratorAggregate, Countable
{
	/** @var PortInfoItemData[] */
	public readonly array $items;

	/** @param PortInfoItemData[] $items */
	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			if (!($item instanceof PortInfoItemData)) {
				throw new \InvalidArgumentException('All items must be instances of ' . PortInfoItemData::class);
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
