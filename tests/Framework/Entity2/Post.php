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

namespace Rekalogika\DomainEvent\Tests\Framework\Entity2;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostRemoved;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
class Post implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: false)]
    private Uuid $id;

    #[ORM\Column]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * @var Collection<array-key,Comment>
     */
    #[ORM\OneToMany(
        targetEntity: Comment::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        indexBy: 'id',
    )]
    private Collection $comments;

    public function __construct(string $title, string $content)
    {
        $this->id = Uuid::v7();
        $this->comments = new ArrayCollection();
        $this->recordEvent(new PostCreated($this));
        $this->title = $title;
        $this->content = $content;
    }

    public function __remove(): void
    {
        $this->recordEvent(new PostRemoved($this));
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
            $this->recordEvent(new PostChanged($this));
        }

        return $this;
    }

    /**
     * @return Collection<array-key,Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
