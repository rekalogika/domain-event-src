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
     * @dataProvider entityManagerDecorationProvider
     */
    public function testEntityManagerDecorationFromContainer(string $serviceId): void
    {
        $entityManager = static::getContainer()->get($serviceId);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);
        // @phpstan-ignore-next-line
        $this->assertInstanceOf(DomainEventAwareEntityManagerInterface::class, $entityManager);
        // @phpstan-ignore-next-line
        $this->assertInstanceOf(ObjectManager::class, $entityManager);
        // @phpstan-ignore-next-line
        $this->assertInstanceOf(DomainEventAwareObjectManager::class, $entityManager);
    }

    /**
     * @return array<int,array<int,string>>
     */
    public static function entityManagerDecorationProvider(): array
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
        $this->assertInstanceOf(DomainEventAwareManagerRegistry::class, $managerRegistry);
        $this->assertInstanceOf(DomainEventAwareManagerRegistryImplementation::class, $managerRegistry);

        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager());
        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager('default'));
        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $managerRegistry->getManager('other'));

        $managers = $managerRegistry->getManagers();

        foreach ($managers as $manager) {
            $this->assertInstanceOf(DomainEventAwareEntityManager::class, $manager);
        }
    }

    public function testEntityManagerDecorationFromRepository(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');
        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $entityManager);

        $repository = $entityManager->getRepository(Book::class);
        $this->assertInstanceOf(BookRepository::class, $repository);
        $this->assertInstanceOf(DomainEventAwareEntityManager::class, $repository->getEntityManager());
    }
}
