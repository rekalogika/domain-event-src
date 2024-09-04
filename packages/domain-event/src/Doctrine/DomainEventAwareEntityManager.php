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
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Event\DomainEventPostFlushDispatchEvent;
use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\EventDispatcher\EventDispatchers;
use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;
use Rekalogika\DomainEvent\Exception\SafeguardTriggeredException;
use Rekalogika\DomainEvent\Exception\UndispatchedEventsException;
use Rekalogika\DomainEvent\Model\DomainEventStore;
use Rekalogika\DomainEvent\Model\TransactionAwareDomainEventStore;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Decorates entity manager so it dispatches domain events after flush.
 */
final class DomainEventAwareEntityManager extends EntityManagerDecorator implements
    DomainEventAwareEntityManagerInterface,
    ResetInterface,
    LazyObjectInterface
{
    private bool $flushEnabled = true;

    private bool $autodispatch = true;

    private readonly DomainEventStore $preFlushDomainEvents;

    private readonly TransactionAwareDomainEventStore $postFlushDomainEvents;

    /**
     * Safeguard for infinite loop
     */
    public static int $preflushLoopLimit = 100;

    public function __construct(
        EntityManagerInterface $wrapped,
        private readonly EventDispatchers $eventDispatchers,
    ) {
        parent::__construct($wrapped);

        $this->preFlushDomainEvents = new DomainEventStore();
        $this->postFlushDomainEvents = new TransactionAwareDomainEventStore();
    }

    public function isUninitializedObject(mixed $value): bool
    {
        if (method_exists($this->wrapped, 'isUninitializedObject')) {
            return $this->wrapped->isUninitializedObject($value);
        }

        return false;
    }

    #[\Override]
    public function isLazyObjectInitialized(bool $partial = false): bool
    {
        if ($this->wrapped instanceof LazyObjectInterface) {
            return $this->wrapped->isLazyObjectInitialized($partial);
        }

        return true;
    }

    #[\Override]
    public function initializeLazyObject(): object
    {
        if ($this->wrapped instanceof LazyObjectInterface) {
            $object = $this->wrapped->initializeLazyObject();

            if ($object instanceof EntityManagerInterface) {
                parent::__construct($object);
            }
        }

        return $this;
    }

    #[\Override]
    public function resetLazyObject(): bool
    {
        if ($this->wrapped instanceof LazyObjectInterface) {
            return $this->wrapped->resetLazyObject();
        }

        return false;
    }

    #[\Override]
    public function getObjectManager(): ObjectManager
    {
        return $this->wrapped;
    }

    #[\Override]
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

    #[\Override]
    public function setAutoDispatchDomainEvents(bool $autoDispatch): void
    {
        $this->autodispatch = $autoDispatch;
    }

    #[\Override]
    public function isAutoDispatchDomainEvents(): bool
    {
        return $this->autodispatch;
    }

    #[\Override]
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
                throw new SafeguardTriggeredException(\sprintf('Pre-flush loop limit reached (%d)', self::$preflushLoopLimit));
            }
        } while ($num > 0);

        $this->flushEnabled = true;

        return $totalDispatched;
    }

    private function preFlushDispatch(): int
    {
        $num = \count($this->preFlushDomainEvents);
        $events = $this->preFlushDomainEvents->pop();

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

    #[\Override]
    public function dispatchPostFlushDomainEvents(): int
    {
        $this->collectEvents();

        $num = \count($this->postFlushDomainEvents);
        $events = $this->postFlushDomainEvents->pop();
        // for safeguard we also clear preflush events here
        $this->preFlushDomainEvents->clear();

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

    #[\Override]
    public function clearDomainEvents(): void
    {
        $this->preFlushDomainEvents->clear();
        $this->postFlushDomainEvents->clear();
    }

    #[\Override]
    public function popDomainEvents(): iterable
    {
        $events = $this->postFlushDomainEvents->pop();
        $this->preFlushDomainEvents->clear();

        return $events;
    }

    #[\Override]
    public function recordDomainEvent(object|iterable $event): void
    {
        $this->preFlushDomainEvents->add($event);
        $this->postFlushDomainEvents->add($event);
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

    #[\Override]
    public function flush(mixed $entity = null): void
    {
        if ($entity !== null) {
            throw new \InvalidArgumentException('Specifying entity to flush is not supported.');
        }

        if (!$this->flushEnabled) {
            $this->clear();
            throw new FlushNotAllowedException();
        }

        if ($this->autodispatch) {
            $this->dispatchPreFlushDomainEvents();
        }

        parent::flush();

        if ($this->autodispatch && !$this->getConnection()->isTransactionActive()) {
            $this->dispatchPostFlushDomainEvents();
        }
    }

    #[\Override]
    public function beginTransaction(): void
    {
        $this->postFlushDomainEvents->beginTransaction();
        parent::beginTransaction();
    }

    #[\Override]
    public function commit(): void
    {
        $this->postFlushDomainEvents->commit();
        parent::commit();

        if ($this->autodispatch && !$this->getConnection()->isTransactionActive()) {
            $this->dispatchPostFlushDomainEvents();
        }
    }

    #[\Override]
    public function rollback(): void
    {
        parent::rollback();
        $this->postFlushDomainEvents->rollback();
    }

    /**
     * @deprecated Use `wrapInTransaction` instead
     */
    public function transactional(mixed $func): mixed
    {
        if (!\is_callable($func)) {
            throw new \InvalidArgumentException('Expected argument of type "callable", got "' . \gettype($func) . '"');
        }

        $this->beginTransaction();

        try {
            /** @psalm-suppress MixedAssignment */
            $return = $func($this);

            $this->flush();
            $this->commit();

            return $return ?: true;
        } catch (\Throwable $e) {
            $this->close();
            $this->rollback();

            throw $e;
        }
    }

    #[\Override]
    public function wrapInTransaction(callable $func): mixed
    {
        $this->getConnection()->beginTransaction();

        try {
            /** @psalm-suppress MixedAssignment */
            $return = $func($this);

            $this->flush();
            $this->commit();

            return $return;
        } catch (\Throwable $e) {
            $this->close();
            $this->rollback();

            throw $e;
        }
    }

    private function hasPendingEvents(): bool
    {
        return \count($this->preFlushDomainEvents) > 0
            || \count($this->postFlushDomainEvents) > 0;
    }

    public function __destruct()
    {
        if ($this->hasPendingEvents()) {
            throw new UndispatchedEventsException(
                $this->preFlushDomainEvents,
                $this->postFlushDomainEvents,
            );
        }
    }
}
