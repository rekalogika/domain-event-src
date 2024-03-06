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

use Doctrine\ORM\Mapping as ORM;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
class Comment implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true, nullable: false)]
    private Uuid $id;

    #[ORM\Column]
    private ?string $content = null;

    #[ORM\ManyToOne(
        targetEntity: Post::class,
        inversedBy: 'comment',
    )]
    private ?Post $post = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function __remove(): void
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

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
