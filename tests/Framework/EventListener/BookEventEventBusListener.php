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

namespace Rekalogika\DomainEvent\Tests\Framework\EventListener;

use Rekalogika\Contracts\DomainEvent\Attribute\AsPublishedDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookRemoved;

final class BookEventEventBusListener
{
    private bool $onCreateCalled = false;
    private bool $onRemoveCalled = false;
    private int $onChangeCalled = 0;

    // skipped for testing purpose
    // #[AsDomainEventBusListener()]
    // public function onCreate(BookCreated $event): void
    // {
    //     $this->onCreateCalled = true;
    // }

    #[AsPublishedDomainEventListener()]
    public function onChange(BookChanged $event): void
    {
        $this->onChangeCalled++;
    }

    #[AsPublishedDomainEventListener()]
    public function onRemove(BookRemoved $event): void
    {
        $this->onRemoveCalled = true;
    }

    public function onCreateCalled(): bool
    {
        return $this->onCreateCalled;
    }

    public function onRemoveCalled(): bool
    {
        return $this->onRemoveCalled;
    }

    public function onChangeCalled(): int
    {
        return $this->onChangeCalled;
    }
}
