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

final class EquatableEventTest extends DomainEventTestCase
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
        $book->setTitle('Book C');
        $book->setTitle('Book D');
        $book->setTitle('Book E');
        $book->setTitle('Book F');

        $this->entityManager->flush();

        $this->assertEquals(1, $preFlushListener->onChangeCalled());
        $this->assertEquals(1, $postFlushListener->onChangeCalled());
    }
}
