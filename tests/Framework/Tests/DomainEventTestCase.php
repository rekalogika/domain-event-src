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

namespace Rekalogika\DomainEvent\Tests\Framework\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Rekalogika\DomainEvent\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\Tests\Framework\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class DomainEventTestCase extends KernelTestCase
{
    // @phpstan-ignore-next-line
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new Kernel();
    }

    public function setUp(): void
    {
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->assertInstanceOf(ManagerRegistry::class, $managerRegistry);

        $entityManager = $managerRegistry->getManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
    }

    public static function getEntityManager(): DomainEventAwareEntityManager
    {
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);
        self::assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);
        $entityManager = $managerRegistry->getManager();
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);

        return $entityManager;
    }
}
