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
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Note;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Review;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event\NoteCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewCreated;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\ChildEntityPreFlushListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;

/**
 * @see https://github.com/rekalogika/domain-event-src/issues/134
 */
final class CascadePersistTest extends DomainEventTestCase
{
    private function getListener(): ChildEntityPreFlushListener
    {
        $listener = static::getContainer()->get(ChildEntityPreFlushListener::class);
        $this->assertInstanceOf(ChildEntityPreFlushListener::class, $listener);

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
     * @param class-string $class
     */
    private function countOutboxMessages(string $class): int
    {
        $outboxReaderFactory = static::getContainer()->get(OutboxReaderFactoryInterface::class);
        $this->assertInstanceOf(OutboxReaderFactoryInterface::class, $outboxReaderFactory);

        $outboxReader = $outboxReaderFactory->createOutboxReader('default');
        $count = 0;

        foreach ($outboxReader->getOutboxMessages(100) as $message) {
            $this->assertInstanceOf(Envelope::class, $message);

            if ($message->getMessage() instanceof $class) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Cascade persist happens during persist(), the review is already in the
     * identity map at pre-flush time.
     */
    public function testCascadePersistOnPersist(): void
    {
        $book = new Book('title', 'description');
        $book->addReview($this->createReview());

        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewCreated::class));
    }

    /**
     * Cascade persist happens during flush(), the review is not yet in the
     * identity map at pre-flush time.
     */
    public function testCascadePersistOnFlush(): void
    {
        $book = $this->persistBook();

        $book->addReview($this->createReview());
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewCreated::class));
    }

    public function testCascadePersistOnFlushOutbox(): void
    {
        $book = $this->persistBook();

        $book->addReview($this->createReview());
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->countOutboxMessages(ReviewCreated::class));
    }

    public function testCascadePersistOnFlushWithUninitializedCollection(): void
    {
        $id = $this->persistBook()->getId();
        static::getEntityManager()->clear();

        $book = static::getEntityManager()->find(Book::class, $id);
        $this->assertInstanceOf(Book::class, $book);

        $book->addReview($this->createReview());
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewCreated::class));
    }

    /**
     * Collecting events must not revive an entity that is removed while still
     * referenced from a cascade-persist association.
     */
    public function testRemoveEntityStillInCascadePersistAssociation(): void
    {
        $book = new Book('title', 'description');
        $review = $this->createReview();
        $book->addReview($review);
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        $reviewId = $review->getId();

        static::getEntityManager()->remove($review);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->assertNull(static::getEntityManager()->find(Review::class, $reviewId));
    }

    /**
     * An entity with a post-insert ID generator is not in the identity map
     * until it is inserted.
     */
    public function testPersistWithPostInsertId(): void
    {
        $book = $this->persistBook();

        $note = new Note();
        $book->addNote($note);
        static::getEntityManager()->persist($note);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(NoteCreated::class));
    }

    public function testCascadePersistOnFlushWithPostInsertId(): void
    {
        $book = $this->persistBook();

        $book->addNote(new Note());
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(NoteCreated::class));
    }

    /**
     * Changes made by a pre-flush listener to an entity that already has
     * pending changes must not cause the earlier changes to be lost.
     */
    public function testChangesInPreFlushListenerArePersisted(): void
    {
        $book = $this->persistBook();
        $id = $book->getId();

        $eventDispatchers = static::getContainer()->get('test.' . Constants::EVENT_DISPATCHERS);
        $this->assertInstanceOf(EventDispatchers::class, $eventDispatchers);

        $dispatcher = $eventDispatchers->getPreFlushEventDispatcher();
        $this->assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $dispatcher->addListener(
            BookChanged::class,
            static function () use ($book): void {
                $book->check();
            },
        );

        $book->setTitle('new title');
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $book = static::getEntityManager()->find(Book::class, $id);
        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame('new title', $book->getTitle());
        $this->assertNotNull($book->getLastChecked());
    }
}
