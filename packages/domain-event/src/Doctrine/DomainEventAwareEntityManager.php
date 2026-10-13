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
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
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
class DomainEventAwareEntityManager extends EntityManagerDecorator implements
    DomainEventAwareEntityManagerInterface,
    ResetInterface
{
    private bool $flushEnabled = true;

    private bool $autodispatch = true;

    private readonly DomainEventStore $preFlushDomainEvents;

    private readonly TransactionAwareDomainEventStore $postFlushDomainEvents;

    /**
     * Entities whose `__remove()` has been called before flush
     *
     * @var \WeakMap<object,true>
     */
    private \WeakMap $earlyRemovals;

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
        $this->earlyRemovals = self::createWeakMap();
    }

    /**
     * @return \WeakMap<object,true>
     */
    private static function createWeakMap(): \WeakMap
    {
        /** @var \WeakMap<object,true> */
        return new \WeakMap();
    }

    public function isUninitializedObject(mixed $value): bool
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (method_exists($this->wrapped, 'isUninitializedObject')) {
            return $this->wrapped->isUninitializedObject($value);
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
        $unitOfWork = $this->getUnitOfWork();

        /** @var array<int,true> */
        $visited = [];

        foreach ($unitOfWork->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $this->collectEventsFromEntity($entity, $visited, false);
            }
        }

        // entities with post-insert ID generators are not in the identity map
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->collectEventsFromEntity($entity, $visited, true);
        }

        foreach ($this->getOrphans() as $orphan) {
            $this->processEarlyRemoval($orphan);
        }
    }

    /**
     * Collects events from the entity, and from new entities reachable from it
     * that will be persisted by cascade during flush.
     *
     * @param array<int,true> $visited
     */
    private function collectEventsFromEntity(
        object $entity,
        array &$visited,
        bool $isNew,
    ): void {
        $oid = spl_object_id($entity);

        if (isset($visited[$oid])) {
            return;
        }

        $visited[$oid] = true;

        if ($entity instanceof DomainEventEmitterInterface) {
            $this->recordDomainEvent($entity->popRecordedEvents());
        }

        if (!$isNew && !$this->isChangeSetComputedOnFlush($entity)) {
            return;
        }

        $unitOfWork = $this->getUnitOfWork();
        $metadata = $this->getClassMetadata($entity::class);

        foreach ($metadata->associationMappings as $association) {
            if (!$association->isCascadePersist()) {
                continue;
            }

            /** @var mixed */
            $value = $metadata->getFieldValue($entity, $association->fieldName);

            if ($value instanceof PersistentCollection) {
                // unwrap so that we don't initialize the collection
                $value = $value->unwrap();
            }

            if (!is_iterable($value)) {
                $value = [$value];
            }

            /** @var mixed $related */
            foreach ($value as $related) {
                if (
                    \is_object($related)
                    && $unitOfWork->getEntityState($related, UnitOfWork::STATE_NEW) === UnitOfWork::STATE_NEW
                ) {
                    $this->collectEventsFromEntity($related, $visited, true);
                }
            }
        }
    }

    /**
     * Whether the unit of work will compute the change set of the managed
     * entity during flush, including cascade persisting its associations and
     * detecting its orphans. Mirrors UnitOfWork::computeChangeSets().
     */
    private function isChangeSetComputedOnFlush(object $entity): bool
    {
        $unitOfWork = $this->getUnitOfWork();

        if (
            $this->isUninitializedObject($entity)
            || $unitOfWork->isReadOnly($entity)
            || $unitOfWork->isScheduledForDelete($entity)
        ) {
            return false;
        }

        if ($unitOfWork->isScheduledForInsert($entity)) {
            return true;
        }

        $metadata = $this->getClassMetadata($entity::class);

        if ($metadata->isReadOnly) {
            return false;
        }

        return $metadata->isChangeTrackingDeferredImplicit()
            || $unitOfWork->isScheduledForDirtyCheck($entity);
    }

    /**
     * Returns the entities that will be removed by orphan removal during
     * flush. The unit of work removes them only after the pre-flush events
     * are dispatched.
     *
     * @return iterable<object>
     */
    private function getOrphans(): iterable
    {
        $unitOfWork = $this->getUnitOfWork();

        // orphans removed from collections are scheduled immediately by
        // PersistentCollection, and unscheduled if added to another collection
        /** @var array<int,object> */
        $orphans = (new \ReflectionProperty(UnitOfWork::class, 'orphanRemovals'))
            ->getValue($unitOfWork);

        yield from $orphans;

        // orphans of to-one associations are detected when computing the
        // change sets, see UnitOfWork::computeChangeSet()
        foreach ($unitOfWork->getIdentityMap() as $className => $entities) {
            $metadata = $this->getClassMetadata($className);
            $associations = [];

            foreach ($metadata->associationMappings as $association) {
                if ($association->isToOne() && $association->orphanRemoval) {
                    $associations[] = $association;
                }
            }

            if ($associations === []) {
                continue;
            }

            foreach ($entities as $entity) {
                if (
                    $unitOfWork->isScheduledForInsert($entity)
                    || !$this->isChangeSetComputedOnFlush($entity)
                ) {
                    continue;
                }

                $originalData = $unitOfWork->getOriginalEntityData($entity);

                foreach ($associations as $association) {
                    /** @var mixed */
                    $original = $originalData[$association->fieldName] ?? null;

                    if (
                        \is_object($original)
                        && $original !== $metadata->getFieldValue($entity, $association->fieldName)
                    ) {
                        yield $original;
                    }
                }
            }
        }
    }

    /**
     * Calls `__remove()` on an entity that will be removed during flush, and
     * on the entities that will be removed with it by cascade. Mirrors
     * UnitOfWork::doRemove().
     */
    private function processEarlyRemoval(object $entity): void
    {
        $unitOfWork = $this->getUnitOfWork();

        if (
            isset($this->earlyRemovals[$entity])
            || $unitOfWork->getEntityState($entity, UnitOfWork::STATE_NEW) !== UnitOfWork::STATE_MANAGED
        ) {
            return;
        }

        $this->earlyRemovals[$entity] = true;

        if ($entity instanceof DomainEventEmitterInterface) {
            $entity->__remove();
            $this->recordDomainEvent($entity->popRecordedEvents());
        }

        $metadata = $this->getClassMetadata($entity::class);

        foreach ($metadata->associationMappings as $association) {
            if (!$association->isCascadeRemove()) {
                continue;
            }

            /** @var mixed */
            $value = $metadata->getFieldValue($entity, $association->fieldName);

            // initializing collections is intended, the unit of work will do
            // the same
            if (!is_iterable($value)) {
                $value = [$value];
            }

            /** @var mixed $related */
            foreach ($value as $related) {
                if (\is_object($related)) {
                    $this->processEarlyRemoval($related);
                }
            }
        }
    }

    /**
     * Whether `__remove()` has been called on the entity before flush, so
     * that it is not called again when the entity is removed during flush.
     *
     * @internal
     */
    public function isRemovedEarly(object $entity): bool
    {
        return isset($this->earlyRemovals[$entity]);
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

        try {
            parent::flush();
        } finally {
            $this->earlyRemovals = self::createWeakMap();
        }

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

    //
    // LazyObjectInterface methods
    //

    public function isLazyObjectInitialized(bool $partial = false): bool
    {
        if ($this->wrapped instanceof LazyObjectInterface) {
            return $this->wrapped->isLazyObjectInitialized($partial);
        }

        return true;
    }

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

    public function resetLazyObject(): bool
    {
        if ($this->wrapped instanceof LazyObjectInterface) {
            return $this->wrapped->resetLazyObject();
        }

        return false;
    }
}
