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

use Rekalogika\Contracts\DomainEvent\Attribute\AsPostFlushDomainEventListener;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookDummyChanged;

final class BookDummyChangedListener
{
    /**
     * @var list<BookDummyChanged>
     */
    public array $preFlush = [];

    /**
     * @var list<BookDummyChanged>
     */
    public array $postFlush = [];

    #[AsPreFlushDomainEventListener()]
    public function onPreFlush(BookDummyChanged $event): void
    {
        $this->preFlush[] = $event;
    }

    #[AsPostFlushDomainEventListener()]
    public function onPostFlush(BookDummyChanged $event): void
    {
        $this->postFlush[] = $event;
    }

}
