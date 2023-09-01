<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent;

use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\DomainEvent\Exception\UndispatchedEventsException;

final class DomainEventManager implements DomainEventManagerInterface
{
    /**
     * @var array<int|string,object>
     */
    private array $preFlushDomainEvents = [];

    /**
     * @var array<int|string,object>
     */
    private array $postFlushDomainEvents = [];

    public function __construct(
        private EventDispatcherInterface $defaultEventDispatcher,
        private EventDispatcherInterface $postFlushEventDispatcher,
        private EventDispatcherInterface $preFlushEventDispatcher,
    ) {
    }

    public function collect(DomainEventEmitterInterface $domainEventEmitter): void
    {
        $events = $domainEventEmitter->popRecordedEvents();

        foreach ($events as $event) {
            $this->recordEvent($event);
        }
    }

    public function recordEvent(object $event): void
    {
        if ($event instanceof EquatableDomainEventInterface) {
            $signature = $event->getSignature();
    
            $this->preFlushDomainEvents[$signature] = $event;
            $this->postFlushDomainEvents[$signature] = $event;
        } else {
            $this->preFlushDomainEvents[] = $event;
            $this->postFlushDomainEvents[] = $event;
        }
    }

    public function preFlushDispatch(): int
    {
        $events = $this->preFlushDomainEvents;
        $num = count($events);
        $this->preFlushDomainEvents = [];

        foreach ($events as $event) {
            $this->preFlushEventDispatcher->dispatch($event);
        }

        return $num;
    }

    public function postFlushDispatch(): int
    {
        $events = $this->postFlushDomainEvents;
        $num = count($events);
        // for safeguard we also clear preflush events here
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];

        foreach ($events as $event) {
            $this->postFlushEventDispatcher->dispatch($event);
            $this->defaultEventDispatcher->dispatch($event);
        }

        return $num;
    }

    public function clear(): void
    {
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];
    }

    public function popEvents(): iterable
    {
        $events = $this->postFlushDomainEvents;
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];

        foreach ($events as $event) {
            yield $event;
        }
    }

    private function hasPendingEvents(): bool
    {
        return count($this->preFlushDomainEvents) > 0
            || count($this->postFlushDomainEvents) > 0;
    }

    public function __destruct()
    {
        if ($this->hasPendingEvents()) {
            throw new UndispatchedEventsException([
                ...$this->preFlushDomainEvents,
                ...$this->postFlushDomainEvents,
            ]);
        }
    }
}
