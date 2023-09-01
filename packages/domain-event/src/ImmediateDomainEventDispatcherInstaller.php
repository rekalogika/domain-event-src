<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent;

use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventImmediateDispatcher;

final class ImmediateDomainEventDispatcherInstaller
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function install(): void
    {
        DomainEventImmediateDispatcher::install($this->eventDispatcher);
    }
}
