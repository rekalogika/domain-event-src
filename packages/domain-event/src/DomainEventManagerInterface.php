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

namespace Rekalogika\DomainEvent;

use Doctrine\Persistence\ObjectManager;

interface DomainEventManagerInterface
{
    /**
     * Gets the original object manager associated with this domain event
     * manager
     */
    public function getObjectManager(): ObjectManager;

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
     *
     * @param object|iterable<object> $event
     */
    public function recordDomainEvent(object|iterable $event): void;
}
