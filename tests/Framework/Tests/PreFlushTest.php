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

use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;
use Rekalogika\DomainEvent\Exception\SafeguardTriggeredException;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookDummyMethodCalledListener;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\BookDummyMethodForNestedRecordEventListener;

final class PreFlushTest extends DomainEventTestCase
{
    #[\Override]
    public function tearDown(): void
    {
        static::getEntityManager()->clearDomainEvents();
        parent::tearDown();
    }

    public function testFlushInPreFlush(): void
    {
        $book = new Book('title', 'description');
        $book->dummyMethodForFlush();
        static::getEntityManager()->persist($book);
        $this->expectException(FlushNotAllowedException::class);
        static::getEntityManager()->flush();
    }

    public function testNestedRecordEvent(): void
    {
        $dummyMethodCalledListener = static::getContainer()->get(BookDummyMethodCalledListener::class);
        $this->assertInstanceOf(BookDummyMethodCalledListener::class, $dummyMethodCalledListener);

        $dummyMethodForNestedRecordEventListener = static::getContainer()->get(BookDummyMethodForNestedRecordEventListener::class);
        $this->assertInstanceOf(BookDummyMethodForNestedRecordEventListener::class, $dummyMethodForNestedRecordEventListener);

        $this->assertFalse($dummyMethodCalledListener->isDummyMethodCalled());
        $this->assertFalse($dummyMethodForNestedRecordEventListener->isDummyMethodForNestedRecordEventCalled());

        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);

        $book->dummyMethodForNestedRecordEvent();
        static::getEntityManager()->flush();

        $this->assertTrue($dummyMethodCalledListener->isDummyMethodCalled());
        $this->assertTrue($dummyMethodForNestedRecordEventListener->isDummyMethodForNestedRecordEventCalled());
    }

    public function testInfiniteLoopSafeguard(): void
    {
        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);

        $book->dummyMethodForInfiniteLoop();
        $this->expectException(SafeguardTriggeredException::class);
        static::getEntityManager()->flush();
    }
}
