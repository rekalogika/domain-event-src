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

use Rekalogika\DomainEvent\Tests\Framework\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
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
        $this->book = $book;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

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
        $this->rating = $rating;

        return $this;
    }
}
