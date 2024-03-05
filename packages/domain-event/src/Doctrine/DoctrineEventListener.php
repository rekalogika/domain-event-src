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
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;

final class DoctrineEventListener
{
    /**
     * @todo fix to support multiple entity managers
     */
    public function __construct(
        private DomainEventAwareEntityManagerInterface $entityManager
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->collectEvents($args->getObject());
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $this->processRemove($args->getObject());
        $this->collectEvents($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->collectEvents($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->collectEvents($args->getObject());
    }

    private function collectEvents(object $entity): void
    {
        if ($entity instanceof DomainEventEmitterInterface) {
            $this->entityManager->collect($entity);
        }
    }

    private function processRemove(object $entity): void
    {
        if ($entity instanceof DomainEventEmitterInterface) {
            $entity->__remove();
        }
    }
}
