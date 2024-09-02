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

namespace Rekalogika\DomainEvent\Event;

use Rekalogika\DomainEvent\DomainEventAwareObjectManager;

class DomainEventPostFlushDispatchEvent
{
    final public function __construct(
        private readonly DomainEventAwareObjectManager $objectManager,
        private readonly object $domainEvent
    ) {}

    public function getDomainEvent(): object
    {
        return $this->domainEvent;
    }

    public function getObjectManager(): DomainEventAwareObjectManager
    {
        return $this->objectManager;
    }
}
