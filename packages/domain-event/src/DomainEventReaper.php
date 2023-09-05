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

use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Clears domain events from DomainEventManager if an exception bubbles up to
 * the kernel. This will prevent DomainEventManager from adding another,
 * possibly confusing error due to the fact there are undispatched events in its
 * queue.
 */
final class DomainEventReaper
{
    public function __construct(
        private DomainEventManagerInterface $domainEventManager
    ) {
    }

    public function onKernelException(): void
    {
        $this->domainEventManager->clear();
    }
}
