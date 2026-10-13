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
use Rekalogika\DomainEvent\Tests\Framework\Entity\Cover;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Review;
use Rekalogika\DomainEvent\Tests\Framework\Event\CoverRemoved;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewRemoved;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\ChildEntityPostFlushListener;
use Rekalogika\DomainEvent\Tests\Framework\EventListener\ChildEntityPreFlushListener;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Uid\Uuid;

/**
 * @see https://github.com/rekalogika/domain-event-src/issues/134
 */
final class CascadeRemoveTest extends DomainEventTestCase
{
    private function getListener(): ChildEntityPreFlushListener
    {
        $listener = static::getContainer()->get(ChildEntityPreFlushListener::class);
        $this->assertInstanceOf(ChildEntityPreFlushListener::class, $listener);

        return $listener;
    }

    private function getPostFlushListener(): ChildEntityPostFlushListener
    {
        $listener = static::getContainer()->get(ChildEntityPostFlushListener::class);
        $this->assertInstanceOf(ChildEntityPostFlushListener::class, $listener);

        return $listener;
    }

    /**
     * Persists a book with a cover, and returns the book's ID.
     */
    private function persistBookWithCover(): Uuid
    {
        $book = new Book('title', 'description');
        $book->setCover(new Cover());

        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        return $book->getId();
    }

    /**
     * Persists a book with a review, and returns the book's ID.
     */
    private function persistBookWithReview(): Uuid
    {
        $review = new Review();
        $review->setBody('body');

        $book = new Book('title', 'description');
        $book->addReview($review);

        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        return $book->getId();
    }

    private function findBook(Uuid $id): Book
    {
        $book = static::getEntityManager()->find(Book::class, $id);
        $this->assertInstanceOf(Book::class, $book);

        return $book;
    }

    private function getFirstReview(Book $book): Review
    {
        $review = $book->getReviews()->first();
        $this->assertInstanceOf(Review::class, $review);

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
     * Cascade remove happens during remove(), the review's preRemove is
     * triggered before pre-flush.
     */
    public function testCascadeRemove(): void
    {
        $book = $this->findBook($this->persistBookWithReview());

        static::getEntityManager()->remove($book);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewRemoved::class));
    }

    public function testCascadeRemoveOutbox(): void
    {
        $book = $this->findBook($this->persistBookWithReview());

        static::getEntityManager()->remove($book);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->countOutboxMessages(ReviewRemoved::class));
    }

    /**
     * Orphan removal happens during flush(), the review's preRemove is not
     * yet triggered at pre-flush time.
     */
    public function testOrphanRemoval(): void
    {
        $book = $this->findBook($this->persistBookWithReview());
        $review = $this->getFirstReview($book);

        $book->removeReview($review);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewRemoved::class));
    }

    public function testOrphanRemovalOutbox(): void
    {
        $book = $this->findBook($this->persistBookWithReview());
        $review = $this->getFirstReview($book);

        $book->removeReview($review);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->countOutboxMessages(ReviewRemoved::class));
    }

    /**
     * Clearing the collection schedules orphan removal of all its elements.
     */
    public function testOrphanRemovalOnClear(): void
    {
        $book = $this->findBook($this->persistBookWithReview());

        $book->getReviews()->clear();
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getListener()->count(ReviewRemoved::class));
    }

    public function testOrphanRemovalRemovesEntity(): void
    {
        $book = $this->findBook($this->persistBookWithReview());
        $reviewId = $this->getFirstReview($book)->getId();

        $book->getReviews()->clear();
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->assertNull(static::getEntityManager()->find(Review::class, $reviewId));
    }

    /**
     * Moving the entity to another collection cancels the orphan removal.
     */
    public function testOrphanMovedToAnotherCollection(): void
    {
        $otherBook = new Book('other title', 'other description');
        static::getEntityManager()->persist($otherBook);
        static::getEntityManager()->flush();
        $otherBookId = $otherBook->getId();

        $book = $this->findBook($this->persistBookWithReview());
        $otherBook = $this->findBook($otherBookId);
        $review = $this->getFirstReview($book);
        $reviewId = $review->getId();

        $book->removeReview($review);
        $otherBook->addReview($review);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->assertSame(0, $this->getListener()->count(ReviewRemoved::class));

        $review = static::getEntityManager()->find(Review::class, $reviewId);
        $this->assertInstanceOf(Review::class, $review);
        $this->assertSame($otherBookId->toRfc4122(), $review->getBook()?->getId()->toRfc4122());
    }

    /**
     * Orphan removal of to-one associations is detected while computing the
     * change sets during flush.
     */
    public function testToOneOrphanRemoval(): void
    {
        $book = $this->findBook($this->persistBookWithCover());
        $coverId = $book->getCover()?->getId();
        $this->assertNotNull($coverId);

        $book->setCover(null);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->assertSame(1, $this->getListener()->count(CoverRemoved::class));
        $this->assertNull(static::getEntityManager()->find(Cover::class, $coverId));
    }

    public function testToOneOrphanRemovalOutbox(): void
    {
        $book = $this->findBook($this->persistBookWithCover());

        $book->setCover(new Cover());
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->countOutboxMessages(CoverRemoved::class));
    }

    /**
     * __remove() must be called only once, even though preRemove is still
     * triggered during flush.
     */
    public function testOrphanRemovalCallsRemoveOnce(): void
    {
        $book = $this->findBook($this->persistBookWithCover());

        $book->setCover(null);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getPostFlushListener()->count(CoverRemoved::class));
    }

    public function testCascadeRemoveCallsRemoveOnce(): void
    {
        $book = new Book('title', 'description');
        $book->setCover(new Cover());
        static::getEntityManager()->persist($book);
        static::getEntityManager()->flush();

        // the cover association has no cascade remove, so remove it explicitly
        $cover = $book->getCover();
        $this->assertInstanceOf(Cover::class, $cover);

        static::getEntityManager()->remove($book);
        static::getEntityManager()->remove($cover);
        static::getEntityManager()->flush();

        $this->assertSame(1, $this->getPostFlushListener()->count(CoverRemoved::class));
    }
}
