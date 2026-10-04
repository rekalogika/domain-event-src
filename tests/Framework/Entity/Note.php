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

namespace Rekalogika\DomainEvent\Tests\Framework\Entity;

use Doctrine\ORM\Mapping as ORM;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\DomainEvent\Tests\Framework\Event\NoteCreated;

/**
 * An entity with a post-insert identifier generator
 */
#[ORM\Entity()]
class Note implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: Book::class,
        inversedBy: 'notes',
    )]
    private ?Book $book = null;

    public function __construct()
    {
        $this->recordEvent(new NoteCreated());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;

        return $this;
    }
}
