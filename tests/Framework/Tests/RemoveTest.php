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
use Symfony\Component\Uid\Uuid;

final class RemoveTest extends DomainEventTestCase
{
    private function persistBook(): Uuid
    {
        $entitymanager = static::getEntityManager();
        $book = new Book('title', 'description');
        $entitymanager->persist($book);
        $entitymanager->flush();
        $entitymanager->clear();

        return $book->getId();
    }

    private function findBook(Uuid $id): Book
    {
        $entitymanager = static::getEntityManager();
        $book = $entitymanager->find(Book::class, $id);
        $this->assertInstanceOf(Book::class, $book);

        return $book;
    }

    public function testImmediateListener(): void
    {
        $id = $this->persistBook();
        $book = $this->findBook($id);

        $listener = static::getContainer()->get(BookEventImmediateListener::class);
        $this->assertInstanceOf(BookEventImmediateListener::class, $listener);

        $this->assertFalse($listener->onRemoveCalled());
        static::getEntityManager()->remove($book);
        $this->assertTrue($listener->onRemoveCalled());

        static::getEntityManager()->flush();
    }

    public function testPrePostFlushListener(): void
    {
        $id = $this->persistBook();
        $book = $this->findBook($id);

        $preFlushListener = static::getContainer()->get(BookEventPreFlushListener::class);
        $this->assertInstanceOf(BookEventPreFlushListener::class, $preFlushListener);

        $postFlushListener = static::getContainer()->get(BookEventPostFlushListener::class);
        $this->assertInstanceOf(BookEventPostFlushListener::class, $postFlushListener);

        $this->assertFalse($preFlushListener->onRemoveCalled());
        $this->assertFalse($postFlushListener->onRemoveCalled());

        static::getEntityManager()->remove($book);
        static::getEntityManager()->flush();

        $this->assertTrue($preFlushListener->onRemoveCalled());
        $this->assertTrue($postFlushListener->onRemoveCalled());
    }
}
