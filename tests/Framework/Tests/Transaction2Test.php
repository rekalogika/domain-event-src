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
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookDummyChangedListener;

final class Transaction2Test extends DomainEventTestCase
{
    private Book $book;
    private BookDummyChangedListener $listener;

    public function setUp(): void
    {
        parent::setUp();

        /** @psalm-suppress PropertyTypeCoercion */
        $this->listener = static::getContainer()->get(BookDummyChangedListener::class);

        $this->book = new Book('Book A', 'Description A');
        $this->entityManager->persist($this->book);
        $this->entityManager->flush();
    }

    public function change(): void
    {
        $this->book->setDummy(base64_encode(random_bytes(10)));
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    public function assertCountPreFlushEvents(int $expected): void
    {
        $this->assertCount($expected, $this->listener->preFlush);
    }

    public function assertCountPostFlushEvents(int $expected): void
    {
        $this->assertCount($expected, $this->listener->postFlush);
    }

    public function testChangeFlush(): void
    {
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(1);
    }

    public function testBeginChangeFlushCommit(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(1);
    }

    public function testBeginChangeFlushRollback(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(0);

        $this->rollback();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(0);
    }

    public function testBeginChangeRollbackFlush(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->rollback();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(1);
    }

    public function testBeginChangeBeginChangeFlushCommitCommit(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(2);
    }

    public function testBeginChangeBeginChangeFlushCommitRollback(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->rollback();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);
    }

    public function testBeginChangeBeginChangeFlushRollbackCommit(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->rollback();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);
    }

    public function testBeginChangeFlushBeginChangeFlushRollbackCommit(): void
    {
        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(0);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(0);

        $this->beginTransaction();
        $this->change();

        $this->assertCountPreFlushEvents(1);
        $this->assertCountPostFlushEvents(0);

        $this->flush();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->rollback();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(0);

        $this->commit();

        $this->assertCountPreFlushEvents(2);
        $this->assertCountPostFlushEvents(1);
    }

}
