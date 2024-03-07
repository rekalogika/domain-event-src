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

namespace Rekalogika\DomainEvent\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Event\DomainEventPostFlushDispatchEvent;
use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\EventDispatcher\EventDispatchers;
use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;
use Rekalogika\DomainEvent\Exception\SafeguardTriggeredException;
use Rekalogika\DomainEvent\Exception\UndispatchedEventsException;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Decorates entity manager so it dispatches domain events after flush.
 */
final class DomainEventAwareEntityManager extends EntityManagerDecorator implements
    DomainEventAwareEntityManagerInterface,
    ResetInterface
{
    private bool $flushEnabled = true;
    private bool $autodispatch = true;

    /**
     * @var array<int|string,object>
     */
    private array $preFlushDomainEvents = [];

    /**
     * @var array<int|string,object>
     */
    private array $postFlushDomainEvents = [];

    /**
     * Safeguard for infinite loop
     */
    public static int $preflushLoopLimit = 100;

    public function __construct(
        EntityManagerInterface $wrapped,
        private EventDispatchers $eventDispatchers,
    ) {
        parent::__construct($wrapped);
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->wrapped;
    }

    public function reset(): void
    {
        $this->flushEnabled = true;
        $this->autodispatch = true;
        $this->clear();
    }

    public function collect(DomainEventEmitterInterface $domainEventEmitter): void
    {
        $events = $domainEventEmitter->popRecordedEvents();

        $this->recordDomainEvent($events);
    }

    public function setAutoDispatchDomainEvents(bool $autoDispatch): void
    {
        $this->autodispatch = $autoDispatch;
    }

    public function isAutoDispatchDomainEvents(): bool
    {
        return $this->autodispatch;
    }

    public function dispatchPreFlushDomainEvents(): int
    {
        $this->flushEnabled = false;
        $totalDispatched = 0;
        $i = 0;

        do {
            $this->collectEvents();
            $num = $this->preFlushDispatch();
            $totalDispatched += $num;
            ++$i;

            if ($i > self::$preflushLoopLimit) {
                throw new SafeguardTriggeredException(sprintf('Pre-flush loop limit reached (%d)', self::$preflushLoopLimit));
            }
        } while ($num > 0);

        $this->flushEnabled = true;

        return $totalDispatched;
    }

    private function preFlushDispatch(): int
    {
        $events = $this->preFlushDomainEvents;
        $num = count($events);
        $this->preFlushDomainEvents = [];

        foreach ($events as $event) {
            $this->eventDispatchers
                ->getPreFlushEventDispatcher()
                ->dispatch($event);

            $this->eventDispatchers
                ->getDefaultEventDispatcher()
                ->dispatch(new DomainEventPreFlushDispatchEvent($this, $event));
        }

        return $num;
    }

    public function dispatchPostFlushDomainEvents(): int
    {
        $events = $this->postFlushDomainEvents;
        $num = count($events);
        // for safeguard we also clear preflush events here
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];

        foreach ($events as $event) {
            $this->eventDispatchers
                ->getPostFlushEventDispatcher()
                ->dispatch($event);

            $this->eventDispatchers
                ->getDefaultEventDispatcher()
                ->dispatch($event);

            $this->eventDispatchers
                ->getDefaultEventDispatcher()
                ->dispatch(new DomainEventPostFlushDispatchEvent($this, $event));
        }

        return $num;
    }

    public function clearDomainEvents(): void
    {
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];
    }

    public function popDomainEvents(): iterable
    {
        $events = $this->postFlushDomainEvents;
        $this->preFlushDomainEvents = [];
        $this->postFlushDomainEvents = [];

        foreach ($events as $event) {
            yield $event;
        }
    }

    public function recordDomainEvent(object|iterable $event): void
    {
        if (is_iterable($event)) {
            foreach ($event as $anEvent) {
                $this->recordDomainEvent($anEvent);
            }

            return;
        }

        if ($event instanceof EquatableDomainEventInterface) {
            $signature = $event->getSignature();

            $this->preFlushDomainEvents[$signature] = $event;
            $this->postFlushDomainEvents[$signature] = $event;
        } else {
            $this->preFlushDomainEvents[] = $event;
            $this->postFlushDomainEvents[] = $event;
        }
    }

    private function collectEvents(): void
    {
        foreach ($this->getUnitOfWork()->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                if ($entity instanceof DomainEventEmitterInterface) {
                    $events = $entity->popRecordedEvents();
                    $this->recordDomainEvent($events);
                }
            }
        }
    }

    public function flush(mixed $entity = null): void
    {
        if (!$this->flushEnabled) {
            $this->clear();
            throw new FlushNotAllowedException();
        }

        if ($this->autodispatch) {
            $this->dispatchPreFlushDomainEvents();
        }

        parent::flush($entity);

        if ($this->autodispatch && !$this->getConnection()->isTransactionActive()) {
            $this->dispatchPostFlushDomainEvents();
        }
    }

    public function commit(): void
    {
        parent::commit();

        if ($this->autodispatch && !$this->getConnection()->isTransactionActive()) {
            $this->dispatchPostFlushDomainEvents();
        }
    }

    public function rollback(): void
    {
        parent::rollback();

        $this->clearDomainEvents();
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
