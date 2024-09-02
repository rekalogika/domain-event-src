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
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\Tests\Framework\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class DomainEventTestCase extends KernelTestCase
{
    protected DomainEventAwareEntityManagerInterface $entityManager;
    protected DomainEventAwareManagerRegistry $managerRegistry;

    // @phpstan-ignore-next-line
    #[\Override]
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new Kernel();
    }

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        // setup manager registry

        $managerRegistry = static::getContainer()->get('doctrine');
        $this->assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);

        $this->managerRegistry = $managerRegistry;

        // create schema

        $managers = $managerRegistry->getManagers();

        foreach ($managers as $manager) {
            $this->assertInstanceOf(EntityManagerInterface::class, $manager);
            $schemaTool = new SchemaTool($manager);
            $schemaTool->createSchema($manager->getMetadataFactory()->getAllMetadata());
        }

        // save entity manager to class property

        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(DomainEventAwareEntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;
    }

    public static function getManagerRegistry(): ManagerRegistry
    {
        $managerRegistry = static::getContainer()->get(ManagerRegistry::class);
        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);
        self::assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);

        return $managerRegistry;
    }

    public static function getEntityManager(): DomainEventAwareEntityManager
    {
        $managerRegistry = static::getManagerRegistry();

        $entityManager = $managerRegistry->getManager();
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);

        return $entityManager;
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();

        $managers = $this->managerRegistry->getManagers();

        foreach ($managers as $manager) {
            $this->assertInstanceOf(DomainEventAwareEntityManagerInterface::class, $manager);
            $manager->clearDomainEvents();
        }
    }
}
