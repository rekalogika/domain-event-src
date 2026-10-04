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

use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Review;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewCreated;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\ReviewEventPreFlushListener;
use Symfony\Component\Messenger\Envelope;

/**
 * @see https://github.com/rekalogika/domain-event-src/issues/134
 */
final class CascadePersistTest extends DomainEventTestCase
{
    private function getListener(): ReviewEventPreFlushListener
    {
        $listener = static::getContainer()->get(ReviewEventPreFlushListener::class);
        $this->assertInstanceOf(ReviewEventPreFlushListener::class, $listener);

        return $listener;
    }

    private function persistBook(): Book
    {
        $book = new Book('title', 'description');
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        return $book;
    }

    private function createReview(): Review
    {
        $review = new Review();
        $review->setBody('body');

        return $review;
    }

    /**
     * @return list<object>
     */
    private function getOutboxMessages(): array
    {
        $outboxReaderFactory = static::getContainer()->get(OutboxReaderFactoryInterface::class);
        $this->assertInstanceOf(OutboxReaderFactoryInterface::class, $outboxReaderFactory);

        $outboxReader = $outboxReaderFactory->createOutboxReader('default');
        $messages = $outboxReader->getOutboxMessages(100);

        $result = [];

        foreach ($messages as $message) {
            $this->assertInstanceOf(Envelope::class, $message);
            $result[] = $message->getMessage();
        }

        return $result;
    }

    /**
     * Cascade persist happens during persist(), the review is already in the
     * identity map at pre-flush time.
     */
    public function testCascadePersistOnPersist(): void
    {
        $listener = $this->getListener();
        $this->assertFalse($listener->onCreateCalled());

        $book = new Book('title', 'description');
        $book->addReview($this->createReview());

        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        $this->assertTrue($listener->onCreateCalled());
    }

    /**
     * Cascade persist happens during flush(), the review is not yet in the
     * identity map at pre-flush time.
     */
    public function testCascadePersistOnFlushPreFlushListener(): void
    {
        $book = $this->persistBook();

        $listener = $this->getListener();
        $this->assertFalse($listener->onCreateCalled());

        $book->addReview($this->createReview());
        static::getEntityManager()->flush();

        $this->assertTrue($listener->onCreateCalled());
    }

    public function testCascadePersistOnFlushOutbox(): void
    {
        $book = $this->persistBook();

        $book->addReview($this->createReview());
        static::getEntityManager()->flush();

        $reviewCreatedMessages = array_filter(
            $this->getOutboxMessages(),
            static fn(object $message): bool => $message instanceof ReviewCreated,
        );

        $this->assertCount(1, $reviewCreatedMessages);
    }
}
