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
use Doctrine\Persistence\ObjectManager;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistryImplementation;
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventAwareObjectManager;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Repository\BookRepository;

final class DecorationTest extends DomainEventTestCase
{
    /**
     * @dataProvider provideEntityManagerDecorationFromContainerCases
     */
    public function testEntityManagerDecorationFromContainer(string $serviceId): void
    {
        $entityManager = static::getContainer()->get($serviceId);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);
        // @phpstan-ignore-next-line
        self::assertInstanceOf(DomainEventAwareEntityManagerInterface::class, $entityManager);
        // @phpstan-ignore-next-line
        self::assertInstanceOf(ObjectManager::class, $entityManager);
        // @phpstan-ignore-next-line
        self::assertInstanceOf(DomainEventAwareObjectManager::class, $entityManager);
    }

    /**
     * @return array<int,array<int,string>>
     */
    public static function provideEntityManagerDecorationFromContainerCases(): iterable
    {
        return [
            ['doctrine.orm.entity_manager'],
            ['doctrine.orm.default_entity_manager'],
            ['doctrine.orm.other_entity_manager'],
            [EntityManagerInterface::class],
            [EntityManagerInterface::class . ' $defaultEntityManager'],
            [EntityManagerInterface::class . ' $otherEntityManager'],
        ];
    }

    public function testEntityManagerDecorationFromRegistry(): void
    {
        $managerRegistry = static::getContainer()->get('doctrine');
        self::assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);
        self::assertInstanceOf(DomainEventAwareManagerRegistryImplementation::class, $managerRegistry);

        self::assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager());
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager('default'));
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager('other'));

        $managers = $managerRegistry->getManagers();

        foreach ($managers as $manager) {
            self::assertInstanceOf(DomainEventAwareEntityManager::class, $manager);
        }
    }

    public function testEntityManagerDecorationFromRepository(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);

        $repository = $entityManager->getRepository(Book::class);
        self::assertInstanceOf(BookRepository::class, $repository);
        self::assertInstanceOf(DomainEventAwareEntityManager::class, $repository->getEntityManager());
    }

    public function testGetManagerNameFromManager(): void
    {
        $managerRegistry = static::getContainer()->get('doctrine');
        self::assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);

        $entityManager = $managerRegistry->getManager();
        self::assertSame('default', $managerRegistry->getManagerName($entityManager));

        $entityManager = $managerRegistry->getManager('other');
        self::assertSame('other', $managerRegistry->getManagerName($entityManager));
    }
}
