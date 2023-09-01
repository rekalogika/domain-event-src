<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests\EventListener;

use Rekalogika\DomainEvent\Tests\Event\EquatableEvent;
use Rekalogika\DomainEvent\Tests\Event\NonEquatableEvent;

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
