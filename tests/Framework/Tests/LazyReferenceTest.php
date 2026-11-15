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

use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Rekalogika\DomainEvent\EventDispatcher\EventDispatchers;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChanged;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Collecting events during flush must not initialize lazy references in the
 * identity map.
 */
final class LazyReferenceTest extends DomainEventTestCase
{
    private function persistBook(): Uuid
    {
        $entityManager = static::getEntityManager();

        $book = new Book('title', 'description');
        $entityManager->persist($book);
        $entityManager->flush();
        $entityManager->clear();

        return $book->getId();
    }

    public function testFlushWithReferenceToMissingRow(): void
    {
        $entityManager = static::getEntityManager();

        $reference = $entityManager->getReference(Book::class, Uuid::v7());
        $this->assertInstanceOf(Book::class, $reference);

        $book = new Book('other', 'description');
        $entityManager->persist($book);
        $entityManager->flush();
        $entityManager->clear();

        $this->assertNotNull($entityManager->find(Book::class, $book->getId()));
    }

    public function testFlushDoesNotInitializeReference(): void
    {
        $id = $this->persistBook();
        $entityManager = static::getEntityManager();

        $reference = $entityManager->getReference(Book::class, $id);
        $this->assertTrue($entityManager->isUninitializedObject($reference));

        $entityManager->flush();

        $this->assertTrue($entityManager->isUninitializedObject($reference));
    }

    public function testEventsAreCollectedFromInitializedReference(): void
    {
        $id = $this->persistBook();
        $entityManager = static::getEntityManager();

        $eventDispatchers = static::getContainer()->get('test.' . Constants::EVENT_DISPATCHERS);
        $this->assertInstanceOf(EventDispatchers::class, $eventDispatchers);

        $dispatcher = $eventDispatchers->getPreFlushEventDispatcher();
        $this->assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $count = 0;
        $dispatcher->addListener(
            BookChanged::class,
            static function () use (&$count): void {
                ++$count;
            },
        );

        $reference = $entityManager->getReference(Book::class, $id);
        $this->assertInstanceOf(Book::class, $reference);

        $reference->setTitle('new title');
        $this->assertFalse($entityManager->isUninitializedObject($reference));

        $entityManager->flush();

        $this->assertSame(1, $count);
    }
}
