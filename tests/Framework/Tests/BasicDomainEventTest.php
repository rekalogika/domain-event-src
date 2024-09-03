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

use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookEventImmediateListener;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookEventPostFlushListener;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookEventPreFlushListener;

final class BasicDomainEventTest extends DomainEventTestCase
{
    public function testImmediateListener(): void
    {
        $listener = static::getContainer()->get(BookEventImmediateListener::class);
        self::assertInstanceOf(BookEventImmediateListener::class, $listener);

        self::assertFalse($listener->onCreateCalled());
        new Book('title', 'description');
        self::assertTrue($listener->onCreateCalled());
    }

    public function testPreFlushListener(): void
    {
        $listener = static::getContainer()->get(BookEventPreFlushListener::class);
        self::assertInstanceOf(BookEventPreFlushListener::class, $listener);

        self::assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        self::assertTrue($listener->onCreateCalled());
    }

    public function testPostFlushListener(): void
    {
        $listener = static::getContainer()->get(BookEventPostFlushListener::class);
        self::assertInstanceOf(BookEventPostFlushListener::class, $listener);

        self::assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        self::assertTrue($listener->onCreateCalled());
    }

    public function testManualPreFlush(): void
    {
        $entityManager = static::getEntityManager();
        $listener = static::getContainer()->get(BookEventPreFlushListener::class);
        self::assertInstanceOf(BookEventPreFlushListener::class, $listener);

        $entityManager->setAutoDispatchDomainEvents(false);

        self::assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        $entityManager->persist($book);
        $entityManager->dispatchPreFlushDomainEvents();
        $entityManager->flush();
        $entityManager->clearDomainEvents();

        self::assertTrue($listener->onCreateCalled());
    }

    public function testManualPostFlush(): void
    {
        $entityManager = static::getEntityManager();
        $listener = static::getContainer()->get(BookEventPostFlushListener::class);
        self::assertInstanceOf(BookEventPostFlushListener::class, $listener);

        $entityManager->setAutoDispatchDomainEvents(false);

        self::assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        $entityManager->persist($book);
        $entityManager->flush();
        $entityManager->dispatchPostFlushDomainEvents();

        self::assertTrue($listener->onCreateCalled());
    }
}
