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
        $this->assertInstanceOf(BookEventImmediateListener::class, $listener);

        $this->assertFalse($listener->onCreateCalled());
        $book = new Book('title', 'description');
        $this->assertTrue($listener->onCreateCalled());
    }

    public function testPreFlushListener(): void
    {
        $listener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $listener);

        $this->assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        $this->assertTrue($listener->onCreateCalled());
    }

    public function testPostFlushListener(): void
    {
        $listener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $listener);

        $this->assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        $this->assertTrue($listener->onCreateCalled());
    }
}
