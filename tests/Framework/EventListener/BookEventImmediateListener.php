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

use Rekalogika\Contracts\DomainEvent\Attribute\AsImmediateDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookRemoved;

final class BookEventImmediateListener
{
    private bool $onCreateCalled = false;

    private bool $onRemoveCalled = false;

    #[AsImmediateDomainEventListener()]
    public function onCreate(BookCreated $event): void
    {
        $this->onCreateCalled = true;
    }

    #[AsImmediateDomainEventListener()]
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
}
