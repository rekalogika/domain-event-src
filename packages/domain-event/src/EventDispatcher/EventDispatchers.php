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

namespace Rekalogika\DomainEvent\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Contains all the different event dispatchers used in the domain event system.
 */
final class EventDispatchers
{
    public function __construct(
        private readonly EventDispatcherInterface $defaultEventDispatcher,
        private readonly EventDispatcherInterface $immediateEventDispatcher,
        private readonly EventDispatcherInterface $preFlushEventDispatcher,
        private readonly EventDispatcherInterface $postFlushEventDispatcher,
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
