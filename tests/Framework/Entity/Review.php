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
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewRemoved;
use Rekalogika\DomainEvent\Tests\Framework\Repository\ReviewRepository;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: false)]
    private Uuid $id;

    /**
     * @var int<1,5>
     */
    #[ORM\Column]
    private int $rating = 3;

    #[ORM\Column]
    private ?string $body = null;

    #[ORM\ManyToOne(
        targetEntity: Book::class,
        inversedBy: 'reviews',
    )]
    private ?Book $book = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->recordEvent(new ReviewCreated($this));
    }

    public function __remove(): void
    {
        $this->recordEvent(new ReviewRemoved($this));
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $originalBook = $this->book;
        $this->book = $book;

        if ($originalBook !== $book) {
            $this->recordEvent(new ReviewChanged($this));
        }

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $originalBody = $this->body;
        $this->body = $body;

        if ($originalBody !== $body) {
            $this->recordEvent(new ReviewChanged($this));
        }

        return $this;
    }

    /**
     * @return int<1,5>
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int<1,5> $rating
     */
    public function setRating(int $rating): self
    {
        $originalRating = $this->rating;
        $this->rating = $rating;

        if ($originalRating !== $rating) {
            $this->recordEvent(new ReviewChanged($this));
        }

        return $this;
    }
}
