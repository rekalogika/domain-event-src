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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChecked;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookDummyMethodForFlushCalled;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookRemoved;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookReviewAdded;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookReviewRemoved;
use Rekalogika\DomainEvent\Tests\Framework\Repository\BookRepository;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: false)]
    private Uuid $id;

    #[ORM\Column]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastChecked = null;

    /**
     * @var Collection<array-key,Review>
     */
    #[ORM\OneToMany(
        targetEntity: Review::class,
        mappedBy: 'book',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        indexBy: 'id',
    )]
    private Collection $reviews;

    public function __construct(string $title, string $description)
    {
        $this->id = Uuid::v7();
        $this->reviews = new ArrayCollection();
        $this->recordEvent(new BookCreated($this));
        $this->setTitle($title);
        $this->setDescription($description);
    }

    public function __remove(): void
    {
        $this->recordEvent(new BookRemoved($this));
    }

    /**
     * We want to check our books' conditions every now and then.
     */
    public function check(): void
    {
        $this->lastChecked = new \DateTimeImmutable();
        $this->recordEvent(new BookChecked($this));
    }

    public function dummyMethodForFlush(): void
    {
        $this->recordEvent(new BookDummyMethodForFlushCalled($this));
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $oldTitle = $this->title;
        $this->title = $title;

        if ($oldTitle !== $title) {
            $this->recordEvent(new BookChanged($this));
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $oldDescription = $this->description;
        $this->description = $description;

        if ($oldDescription !== $description) {
            $this->recordEvent(new BookChanged($this));
        }

        return $this;
    }

    /**
     * @return Collection<array-key,Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setBook($this);
            $this->recordEvent(new BookReviewAdded($this, $review));
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getBook() === $this) {
                $review->setBook(null);
            }
            $this->recordEvent(new BookReviewRemoved($this, $review));
        }

        return $this;
    }

    public function getLastChecked(): ?\DateTimeInterface
    {
        return $this->lastChecked;
    }
}
