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

/**
 * Clears domain events from DomainEventAwareEntityManager if an exception
 * bubbles up to the kernel. This will prevent DomainEventAwareEntityManager
 * from adding another, possibly confusing error due to the fact there are
 * undispatched events in its queue.
 */
final class DomainEventReaper
{
    /**
     * @param iterable<DomainEventAwareEntityManagerInterface> $entityManagers
     */
    public function __construct(
        private iterable $entityManagers,
    ) {
    }

    public function onKernelException(): void
    {
        foreach ($this->entityManagers as $entityManager) {
            $entityManager->clearDomainEvents();
        }
    }
}
