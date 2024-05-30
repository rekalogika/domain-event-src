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
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostRemoved;

final class PostEventEventBusListener
{
    private bool $onCreateCalled = false;
    private bool $onRemoveCalled = false;
    private int $onChangeCalled = 0;

    // skipped for testing purpose
    // #[AsDomainEventBusListener()]
    // public function onCreate(PostCreated $event): void
    // {
    //     $this->onCreateCalled = true;
    // }

    #[AsPublishedDomainEventListener()]
    public function onChange(PostChanged $event): void
    {
        $this->onChangeCalled++;
    }

    #[AsPublishedDomainEventListener()]
    public function onRemove(PostRemoved $event): void
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
