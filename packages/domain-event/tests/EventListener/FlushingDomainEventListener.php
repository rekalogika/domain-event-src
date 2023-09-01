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

use Doctrine\ORM\EntityManagerInterface;

final class FlushingDomainEventListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private bool $entityCreatedHeard = false;
    private bool $entityRemovedHeard = false;
    private bool $entityNameChangedHeard = false;

    public function onEntityCreated(): void
    {
        $this->entityManager->flush();
        $this->entityCreatedHeard = true;
    }

    public function onEntityRemoved(): void
    {
        $this->entityManager->flush();
        $this->entityRemovedHeard = true;
    }

    public function onEntityNameChanged(): void
    {
        $this->entityManager->flush();
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
