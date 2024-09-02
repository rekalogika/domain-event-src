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

namespace Rekalogika\DomainEvent\Tests\Framework\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookDummyMethodForNestedRecordEventCalled;

final class BookDummyMethodForNestedRecordEventListener
{
    private bool $dummyMethodForNestedRecordEventCalled = false;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[AsPreFlushDomainEventListener()]
    public function onDummyMethodCalled(BookDummyMethodForNestedRecordEventCalled $event): void
    {
        $bookId = $event->getId();
        $book = $this->entityManager->find(Book::class, $bookId);
        \assert($book instanceof Book);
        $book->dummyMethod();

        $this->dummyMethodForNestedRecordEventCalled = true;
    }

    public function isDummyMethodForNestedRecordEventCalled(): bool
    {
        return $this->dummyMethodForNestedRecordEventCalled;
    }
}
