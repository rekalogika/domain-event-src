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

namespace Rekalogika\DomainEvent\Contracts;

use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;

interface DomainEventManagerInterface
{
    /**
     * Collects all events from a DomainEventEmitter
     */
    public function collect(DomainEventEmitterInterface $domainEventEmitter): void;

    /**
     * Dispatch pre-flush domain events
     */
    public function preFlushDispatch(): int;

    /**
     * Dispatch post-flush domain events
     */
    public function postFlushDispatch(): int;

    /**
     * Clears all events
     */
    public function clear(): void;

    /**
     * Records a domain event
     */
    public function recordEvent(object $event): void;

    /**
     * Clears and return all events
     *
     * @return iterable<int|string,object>
     */
    public function popEvents(): iterable;
}
