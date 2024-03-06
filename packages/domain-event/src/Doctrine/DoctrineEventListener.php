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

namespace Rekalogika\DomainEvent\Doctrine;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\Persistence\ObjectManager;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;

final class DoctrineEventListener
{
    public function __construct(
        private ObjectManagerDecoratorResolverInterface $objectManagerDecoratorResolver,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->collectEvents($args->getObject(), $args->getObjectManager());
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $this->processRemove($args->getObject());
        $this->collectEvents($args->getObject(), $args->getObjectManager());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->collectEvents($args->getObject(), $args->getObjectManager());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->collectEvents($args->getObject(), $args->getObjectManager());
    }

    private function collectEvents(object $entity, ObjectManager $objectManager): void
    {
        if (!$entity instanceof DomainEventEmitterInterface) {
            return;
        }

        $decoratedObjectManager = $this->objectManagerDecoratorResolver
            ->getDecoratedObjectManager($objectManager);

        $events = $entity->popRecordedEvents();
        $decoratedObjectManager->recordDomainEvent($events);
    }

    private function processRemove(object $entity): void
    {
        if ($entity instanceof DomainEventEmitterInterface) {
            $entity->__remove();
        }
    }
}
