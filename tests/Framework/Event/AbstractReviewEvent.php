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

use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Review;
use Symfony\Component\Uid\Uuid;

abstract class AbstractReviewEvent implements EquatableDomainEventInterface
{
    private readonly Uuid $id;

    public function __construct(Review $review)
    {
        $this->id = $review->getId();
    }

    final public function getId(): Uuid
    {
        return $this->id;
    }

    #[\Override]
    final public function getSignature(): string
    {
        return hash('xxh128', serialize($this));
    }
}
