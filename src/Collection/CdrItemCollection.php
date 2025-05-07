<?php

declare(strict_types=1);

namespace Besir\Dinstar\Collection;

use ArrayIterator;
use Besir\Dinstar\Data\CdrItemData;
use Besir\Dinstar\Data\IData;
use Countable;
use IteratorAggregate;

class CdrItemCollection implements IteratorAggregate, Countable, IData
{
	/** @var CdrItemData[] */
	public readonly array $items;

	/** @param CdrItemData[] $items */
	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			if (!($item instanceof CdrItemData)) {
				throw new \InvalidArgumentException('All items must be instances of ' . CdrItemData::class);
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
