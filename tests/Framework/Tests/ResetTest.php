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
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Symfony\Contracts\Service\ResetInterface;

final class ResetTest extends DomainEventTestCase
{
    public function testEntityManagerReset(): void
    {
        $entitymanager = static::getEntityManager();
        $entitymanager->reset();
    }

    public function testManagerRegistryResetManager(): void
    {
        $managerRegistry = static::getManagerRegistry();
        $managerRegistry->resetManager();
    }

    public function testResetManagerReopensClosedEntityManager(): void
    {
        $managerRegistry = static::getManagerRegistry();
        $entityManager = static::getEntityManager();

        $entityManager->close();
        $this->assertFalse($entityManager->isOpen());

        $resetEntityManager = $managerRegistry->resetManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $resetEntityManager);
        $this->assertTrue($resetEntityManager->isOpen());

        $this->assertTrue(static::getEntityManager()->isOpen());
        $this->assertTrue($entityManager->isOpen());

        $containerEntityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $containerEntityManager);
        $this->assertTrue($containerEntityManager->isOpen());

        $this->assertCanFlush();
    }

    public function testRegistryResetReopensClosedEntityManager(): void
    {
        $managerRegistry = static::getManagerRegistry();
        $entityManager = static::getEntityManager();

        $entityManager->close();
        $this->assertFalse($entityManager->isOpen());

        $this->assertInstanceOf(ResetInterface::class, $managerRegistry);
        $managerRegistry->reset();

        $this->assertTrue(static::getEntityManager()->isOpen());
        $this->assertTrue($entityManager->isOpen());

        $this->assertCanFlush();
    }

    public function testResetManagerOnUninitializedEntityManager(): void
    {
        $resetEntityManager = static::getManagerRegistry()->resetManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $resetEntityManager);
        $this->assertTrue($resetEntityManager->isOpen());
    }

    private function assertCanFlush(): void
    {
        $entityManager = static::getEntityManager();
        $book = new Book('title', 'description');
        $entityManager->persist($book);
        $entityManager->flush();
        $entityManager->clearDomainEvents();

        $this->assertTrue($entityManager->contains($book));
    }
}
