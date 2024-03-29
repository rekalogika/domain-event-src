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

namespace Rekalogika\DomainEvent\Tests\Integration\Model;

use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterTrait;
use Rekalogika\DomainEvent\Tests\Integration\Event\EntityCreated;
use Rekalogika\DomainEvent\Tests\Integration\Event\EntityNameChanged;
use Rekalogika\DomainEvent\Tests\Integration\Event\EntityRemoved;
use Rekalogika\DomainEvent\Tests\Integration\Event\EquatableEvent;
use Rekalogika\DomainEvent\Tests\Integration\Event\NonEquatableEvent;

final class Entity implements DomainEventEmitterInterface
{
    use DomainEventEmitterTrait;

    private string $id;

    public function __construct(
        private string $name,
    ) {
        $this->id = bin2hex(random_bytes(16));

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
