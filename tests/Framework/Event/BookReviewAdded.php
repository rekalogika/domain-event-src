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

namespace Rekalogika\DomainEvent\Tests\Framework\Event;

use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Review;
use Symfony\Component\Uid\Uuid;

final class BookReviewAdded extends AbstractBookEvent
{
    private Uuid $reviewId;

    public function __construct(Book $book, Review $review)
    {
        parent::__construct($book);
        $this->reviewId = $review->getId();
    }

    public function getReviewId(): Uuid
    {
        return $this->reviewId;
    }
}
