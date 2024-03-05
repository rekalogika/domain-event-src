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

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatchers
{
    public function __construct(
        private EventDispatcherInterface $defaultEventDispatcher,
        private EventDispatcherInterface $immediateEventDispatcher,
        private EventDispatcherInterface $preFlushEventDispatcher,
        private EventDispatcherInterface $postFlushEventDispatcher,
    ) {
    }

    public function getDefaultEventDispatcher(): EventDispatcherInterface
    {
        return $this->defaultEventDispatcher;
    }

    public function getImmediateEventDispatcher(): EventDispatcherInterface
    {
        return $this->immediateEventDispatcher;
    }

    public function getPreFlushEventDispatcher(): EventDispatcherInterface
    {
        return $this->preFlushEventDispatcher;
    }

    public function getPostFlushEventDispatcher(): EventDispatcherInterface
    {
        return $this->postFlushEventDispatcher;
    }
}
