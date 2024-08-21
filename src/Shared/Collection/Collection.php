<?php

declare(strict_types=1);

namespace Siesta\Shared\Collection;

use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
abstract class Collection implements Countable, IteratorAggregate
{
    /** @param array<int, mixed> $items */
    public function __construct(private array $items)
    {
        self::arrayOf($this->type(), $items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items());
    }

    public function count(): int
    {
        return \count($this->items());
    }

    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    public function remove(mixed $itemToRemove): void
    {
        foreach ($this->getIterator() as $key => $item) {
            if ($item === $itemToRemove) {
                unset($this->items[$key]);
            }
        }
    }

    /** @return array<TValue>> */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @param Collection<TKey, TValue> $collection
     *
     * @throws \Exception
     */
    public function merge(Collection $collection): void
    {
        foreach ($collection->getIterator() as $iterator) {
            if ($this->type() !== \get_class($iterator) && $this->type() !== get_parent_class($iterator)) {
                throw new Exception(
                    sprintf('Items must be <%s> instead of <%s>', \get_class($iterator), $this->type())
                );
            }
            $this->add($iterator);
        }
    }
    abstract protected function type(): string;

    public static function arrayOf(string $class, array $items): void
    {
        foreach ($items as $item) {
            self::instanceOf($class, $item);
        }
    }

    public static function instanceOf(string $class, mixed $item): void
    {
        if (!$item instanceof $class) {
            throw new \InvalidArgumentException(
                sprintf('The object <%s> is not an instance of <%s>', $class, \get_class($item))
            );
        }
    }


}
