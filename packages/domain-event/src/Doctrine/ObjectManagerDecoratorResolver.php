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

use Doctrine\Persistence\ObjectManager;
use Rekalogika\DomainEvent\DomainEventManagerInterface;

final class ObjectManagerDecoratorResolver implements ObjectManagerDecoratorResolverInterface
{
    /**
     * @var \WeakMap<ObjectManager,DomainEventManagerInterface>
     */
    private \WeakMap $objectManagerToDecoratedObjectManager;

    /**
     * @param iterable<DomainEventManagerInterface> $decoratedObjectManagers
     */
    public function __construct(iterable $decoratedObjectManagers)
    {
        /** @var \WeakMap<ObjectManager,DomainEventManagerInterface> */
        $weakMap = new \WeakMap();
        $this->objectManagerToDecoratedObjectManager = $weakMap;

        foreach ($decoratedObjectManagers as $decoratedObjectManager) {
            if ($decoratedObjectManager instanceof DomainEventAwareEntityManager) {
                $this->objectManagerToDecoratedObjectManager[$decoratedObjectManager->getObjectManager()] =  $decoratedObjectManager;
            }
        }
    }

    public function getDecoratedObjectManager(
        ObjectManager $objectManager
    ): ObjectManager&DomainEventManagerInterface {
        if ($objectManager instanceof DomainEventManagerInterface) {
            return $objectManager;
        }

        $domainEventManager = $this->objectManagerToDecoratedObjectManager[$objectManager] ?? null;

        if ($domainEventManager === null) {
            throw new \InvalidArgumentException('Object manager is not decorated');
        }

        if (!$domainEventManager instanceof ObjectManager) {
            throw new \InvalidArgumentException('Object manager is not decorated');
        }

        return $domainEventManager;
    }
}
