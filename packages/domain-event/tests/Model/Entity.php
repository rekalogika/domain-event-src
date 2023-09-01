<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests\Model;

use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\DomainEvent\Tests\Event\EntityCreated;
use Rekalogika\DomainEvent\Tests\Event\EntityNameChanged;
use Rekalogika\DomainEvent\Tests\Event\EntityRemoved;
use Rekalogika\DomainEvent\Tests\Event\EquatableEvent;
use Rekalogika\DomainEvent\Tests\Event\NonEquatableEvent;
use Symfony\Component\Uid\Uuid;

final class Entity implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    private string $id;

    public function __construct(
        private string $name,
    ) {
        $this->id = (Uuid::v7())->toRfc4122();

        $this->recordEvent(new EntityCreated($this));
    }

    public function __remove(): void
    {
        $this->recordEvent(new EntityRemoved($this));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->recordEvent(new EntityNameChanged($this));

        return $this;
    }

    public function equatableCheck(): void
    {
        $this->recordEvent(new NonEquatableEvent($this));
        $this->recordEvent(new NonEquatableEvent($this));
        $this->recordEvent(new NonEquatableEvent($this));
        $this->recordEvent(new EquatableEvent($this));
        $this->recordEvent(new EquatableEvent($this));
        $this->recordEvent(new EquatableEvent($this));
    }
}
