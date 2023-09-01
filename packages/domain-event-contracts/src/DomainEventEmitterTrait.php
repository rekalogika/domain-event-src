<?php

/*
 * This file is part of rekalogika/domain-event-contracts package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Contracts\DomainEvent;

/**
 * Helper trait to implement DomainEventEmitterInterface.
 */
trait DomainEventEmitterTrait
{
    /**
     * @var array<int|string,object>
     */
    private array $recordedDomainEvents = [];

    /**
     * @return array<int|string,object>
     */
    final public function popRecordedEvents(): array
    {
        $recordedEvents = $this->recordedDomainEvents;
        $this->recordedDomainEvents = [];

        return $recordedEvents;
    }

    /**
     * Called by the object to record an event, and immediately dispatch it
     * using the DomainEventImmediateDispatcher. Returns the event object as
     * returned by the immediate dispatcher. It does not get anything back from
     * preflush or postflush events.
     *
     * @template T of object
     * @param T $event
     * @return T
     */
    protected function recordEvent(
        object $event
    ): object {
        if ($event instanceof EquatableDomainEventInterface) {
            $hash = $event->getSignature();
            $this->recordedDomainEvents[$hash] = $event;
        } else {
            $this->recordedDomainEvents[] = $event;
        }

        // returns the event object as returned by the immediate dispatcher
        return DomainEventImmediateDispatcher::dispatch($event);
    }

    public function __remove(): void
    {
    }
}
