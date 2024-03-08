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

namespace Rekalogika\DomainEvent\Outbox\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\Outbox\Entity\OutgoingEvent;

class DomainEventDispatchListener
{
    public function onPreFlushDispatch(DomainEventPreFlushDispatchEvent $event): void
    {
        $domainEvent = $event->getDomainEvent();
        $objectManager = $event->getObjectManager();

        if (!$objectManager instanceof EntityManagerInterface) {
            return;
        }

        $objectManager->persist(new OutgoingEvent($domainEvent));
    }
}
