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

final class DomainEventListener
{
    private bool $entityCreatedHeard = false;

    private bool $entityRemovedHeard = false;

    private bool $entityNameChangedHeard = false;

    public function onEntityCreated(): void
    {
        $this->entityCreatedHeard = true;
    }

    public function onEntityRemoved(): void
    {
        $this->entityRemovedHeard = true;
    }

    public function onEntityNameChanged(): void
    {
        $this->entityNameChangedHeard = true;
    }

    public function isEntityCreatedHeard(): bool
    {
        return $this->entityCreatedHeard;
    }

    public function isEntityRemovedHeard(): bool
    {
        return $this->entityRemovedHeard;
    }

    public function isEntityNameChangedHeard(): bool
    {
        return $this->entityNameChangedHeard;
    }
}
