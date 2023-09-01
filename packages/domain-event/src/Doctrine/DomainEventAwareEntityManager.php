<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Doctrine;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;

/**
 * Decorates entity manager so it dispatches domain events after flush.
 */
final class DomainEventAwareEntityManager extends EntityManagerDecorator implements
    DomainEventAwareEntityManagerInterface
{
    private bool $flushEnabled = true;
    private bool $autodispatch = true;
    private DomainEventEmitterCollectorInterface $collector;

    public function __construct(
        EntityManagerInterface $wrapped,
        private DomainEventManagerInterface $domainEventManager,
        ?DomainEventEmitterCollectorInterface $collector = null
    ) {
        parent::__construct($wrapped);

        if (null === $collector) {
            $this->collector = new DomainEventEmitterCollector();
        } else {
            $this->collector = $collector;
        }
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

        do {
            $this->collectEvents();
            $num = $this->domainEventManager->preFlushDispatch();
            $totalDispatched += $num;
        } while ($num > 0);

        $this->flushEnabled = true;

        return $totalDispatched;
    }

    public function dispatchPostFlushDomainEvents(): int
    {
        return $this->domainEventManager->postFlushDispatch();
    }

    public function clearDomainEvents(): void
    {
        $this->domainEventManager->clear();
    }

    public function popDomainEvents(): iterable
    {
        return $this->domainEventManager->popEvents();
    }

    public function recordDomainEvent(object $event): void
    {
        $this->domainEventManager->recordEvent($event);
    }

    public function recordDomainEvents(iterable $events): void
    {
        foreach ($events as $event) {
            $this->domainEventManager->recordEvent($event);
        }
    }

    private function collectEvents(): void
    {
        $entities = $this->collector->collectEntities($this->getUnitOfWork());

        foreach ($entities as $entity) {
            if ($entity instanceof DomainEventEmitterInterface) {
                $this->domainEventManager->collect($entity);
            }
        }
    }

    public function flush(mixed $entity = null): void
    {
        if (!$this->flushEnabled) {
            $this->domainEventManager->clear();
            throw new FlushNotAllowedException();
        }

        $this->dispatchPreFlushDomainEvents();
        parent::flush($entity);
        $this->dispatchPostFlushDomainEvents();
    }
}
