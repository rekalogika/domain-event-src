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

use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Event\NoteCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event\ReviewRemoved;

final class ChildEntityPreFlushListener
{
    /**
     * @var list<ReviewCreated|ReviewRemoved|NoteCreated>
     */
    public array $events = [];

    #[AsPreFlushDomainEventListener()]
    public function onReviewCreated(ReviewCreated $event): void
    {
        $this->events[] = $event;
    }

    #[AsPreFlushDomainEventListener()]
    public function onReviewRemoved(ReviewRemoved $event): void
    {
        $this->events[] = $event;
    }

    #[AsPreFlushDomainEventListener()]
    public function onNoteCreated(NoteCreated $event): void
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
