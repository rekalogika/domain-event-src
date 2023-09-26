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

namespace Rekalogika\Contracts\DomainEvent;

/**
 * Interface implemented by classes that records domain events, typically your
 * domain entities.
 */
interface DomainEventEmitterInterface
{
    /**
     * Returns all domain events recorded by the entity, and delete them.
     *
     * @return array<int|string,object>
     */
    public function popRecordedEvents(): array;

    /**
     * Called when the object is removed from the persistence layer.
     */
    public function __remove(): void;
}
