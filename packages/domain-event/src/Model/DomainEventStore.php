<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/domain-event-src package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Model;

use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;

/**
 * @implements \IteratorAggregate<int|string,object>
 */
class DomainEventStore implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int|string,object>
     */
    private array $events = [];

    /**
     * Adds an event to the store.
     *
     * @param object|iterable<object> $event
     */
    public function add(object|iterable $event): void
    {
        if (is_iterable($event)) {
            foreach ($event as $anEvent) {
                $this->add($anEvent);
            }

            return;
        }

        if ($event instanceof EquatableDomainEventInterface) {
            $signature = $event->getSignature();

            $this->events[$signature] = $event;
        } else {
            $this->events[] = $event;
        }
    }

    public function clear(): void
    {
        $this->events = [];
    }

    /**
     * Return the events and clear the store.
     *
     * @return iterable<object>
     */
    public function pop(): iterable
    {
        $events = $this->events;
        $this->clear();

        yield from $events;
    }

    /**
     * @return \Traversable<int|string,object>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->events;
    }

    public function count(): int
    {
        return \count($this->events);
    }
}
