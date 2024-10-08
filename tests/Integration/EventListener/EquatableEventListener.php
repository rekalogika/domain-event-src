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

namespace Rekalogika\DomainEvent\Tests\Integration\EventListener;

use Rekalogika\DomainEvent\Tests\Integration\Event\EquatableEvent;
use Rekalogika\DomainEvent\Tests\Integration\Event\NonEquatableEvent;

final class EquatableEventListener
{
    private int $equatableEventHeard = 0;

    private int $nonEquatableEventHeard = 0;

    public function onEquatableEvent(EquatableEvent $event): void
    {
        $this->equatableEventHeard++;
    }

    public function onNonEquatableEvent(NonEquatableEvent $event): void
    {
        $this->nonEquatableEventHeard++;
    }

    public function getEquatableEventHeard(): int
    {
        return $this->equatableEventHeard;
    }

    public function getNonEquatableEventHeard(): int
    {
        return $this->nonEquatableEventHeard;
    }
}
