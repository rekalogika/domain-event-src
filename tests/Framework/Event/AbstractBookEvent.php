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
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Symfony\Component\Uid\Uuid;

abstract class AbstractBookEvent implements EquatableDomainEventInterface
{
    private Uuid $id;

    public function __construct(Book $book)
    {
        $this->id = $book->getId();
    }

    final public function getId(): Uuid
    {
        return $this->id;
    }

    final public function getSignature(): string
    {
        return hash('xxh128', serialize($this));
    }
}
