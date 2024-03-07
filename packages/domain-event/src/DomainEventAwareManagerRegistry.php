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

namespace Rekalogika\DomainEvent;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

interface DomainEventAwareManagerRegistry extends ManagerRegistry
{
    public function getRealRegistry(): ManagerRegistry;

    public function getDomainEventAwareManager(
        ObjectManager $objectManager
    ): DomainEventAwareObjectManager;

    /**
     * @return array<string,DomainEventAwareObjectManager>
     */
    public function getDomainEventAwareManagers(): array;

    /**
     * @param class-string $class
     * @return null|DomainEventAwareObjectManager
     */
    public function getDomainEventAwareManagerForClass(
        string $class
    ): ?DomainEventAwareObjectManager;

}
