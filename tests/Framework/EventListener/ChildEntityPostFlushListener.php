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
use Rekalogika\DomainEvent\Tests\Framework\Event\CoverRemoved;

final class ChildEntityPostFlushListener
{
    /**
     * @var list<CoverRemoved>
     */
    public array $events = [];

    #[AsPostFlushDomainEventListener()]
    public function onCoverRemoved(CoverRemoved $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @param class-string $class
     */
    public function count(string $class): int
    {
        return \count(array_filter(
            $this->events,
            static fn(object $event): bool => $event instanceof $class,
        ));
    }
}
