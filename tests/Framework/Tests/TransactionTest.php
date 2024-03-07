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
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookEventPostFlushListener;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookEventPreFlushListener;

final class TransactionTest extends DomainEventTestCase
{
    public function testWithoutTransaction(): void
    {
        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $book = new Book('Book A', 'Description A');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $book->setTitle('Book B');
        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(1, $postFlushListener->onChangeCalled());
    }

    public function testTransaction(): void
    {
        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $book = new Book('Book A', 'Description A');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $this->entityManager->beginTransaction();

        $book->setTitle('Book B');
        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $this->entityManager->commit();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(1, $postFlushListener->onChangeCalled());
    }

    public function testNestedTransaction(): void
    {
        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $book = new Book('Book A', 'Description A');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $this->entityManager->beginTransaction(); // first transaction

        $book->setTitle('Book B');
        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $this->entityManager->beginTransaction(); // second transaction

        $book->setTitle('Book C');
        $this->entityManager->flush();

        $this->assertEquals(2, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $this->entityManager->commit(); // first commit

        $this->assertEquals(2, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $this->entityManager->commit(); // second commit

        $this->assertEquals(2, $preFlushListener->onChangeCalled());
        $this->assertEquals(1, $postFlushListener->onChangeCalled());
    }

    public function testRollbackTransaction(): void
    {
        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $book = new Book('Book A', 'Description A');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $this->entityManager->beginTransaction();

        $book->setTitle('Book B');
        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $this->entityManager->rollback();
        $events = $book->popRecordedEvents();

        $this->assertEmpty($events);
        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());
    }

    public function testEventsInQueueBeforeRollbackTransaction(): void
    {
        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $book = new Book('Book A', 'Description A');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $this->entityManager->beginTransaction();

        $book->setTitle('Book B');
        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());

        $events = $this->entityManager->popDomainEvents();
        $numEvents = 0;
        foreach ($events as $event) {
            $numEvents++;
        }

        $this->entityManager->rollback();

        $this->assertGreaterThan(0, $numEvents);
        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(0, $postFlushListener->onChangeCalled());
    }
}
