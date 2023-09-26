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

namespace Rekalogika\DomainEvent\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;

class Factory
{
    public static function mockEntityManager(): EntityManagerInterface
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }

    public static function mockManagerRegistry(): ManagerRegistry
    {
        $managerRegistry = \Mockery::mock(ManagerRegistry::class);
        assert($managerRegistry instanceof ManagerRegistry);

        return $managerRegistry;
    }

    public static function mockEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        assert($eventDispatcher instanceof EventDispatcherInterface);

        return $eventDispatcher;
    }
}
