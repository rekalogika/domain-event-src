<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Contracts;

use Doctrine\ORM\EntityManagerInterface;

interface DomainEventAwareEntityManagerInterface extends EntityManagerInterface
{
    /**
     * Enables or disables auto dispatch
     */
    public function setAutoDispatchDomainEvents(bool $autoDispatch): void;

    /**
     * Returns true if auto dispatch is enabled
     */
    public function isAutoDispatchDomainEvents(): bool;

    /**
     * Manually dispatch all pending events before flushing
     */
    public function dispatchPreFlushDomainEvents(): int;

    /**
     * Manually dispatch all pending events after flushing
     */
    public function dispatchPostFlushDomainEvents(): int;

    /**
     * Clears all pending events without dispatching
     */
    public function clearDomainEvents(): void;

    /**
     * Returns and clears all pending domain events. This is taken from the
     * post-flush queue.
     *
     * @return iterable<object>
     */
    public function popDomainEvents(): iterable;

    /**
     * Manually adds a domain event to the queue, to be dispatched later. This
     * is added to both the pre-flush and post-flush queues.
     */
    public function recordDomainEvent(object $event): void;
    
    /**
     * Manually adds several domain events to the queue. They will be added to
     * both the pre-flush and post-flush queues.
     *
     * @param iterable<object> $events
     * @return void
     */
    public function recordDomainEvents(iterable $events): void;
}
