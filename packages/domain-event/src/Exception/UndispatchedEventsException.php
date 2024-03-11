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

namespace Rekalogika\DomainEvent\Exception;

use Rekalogika\DomainEvent\Model\DomainEventStore;

class UndispatchedEventsException extends LogicException
{
    public function __construct(DomainEventStore $preFlushEvents, DomainEventStore $postFlushEvents)
    {
        $num = count($preFlushEvents) + count($postFlushEvents);

        parent::__construct(sprintf('There are still %d undispatched domain events. If you disable autodispatch, you have to dispatch them manually or clear them.', $num));
    }
}
