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
    /**
     * Gets the real registry that is being decorated by this instance.
     */
    public function getRealRegistry(): ManagerRegistry;

    /**
     * Gets the manager name of the given object manager.
     */
    public function getManagerName(ObjectManager $manager): string;

    /**
     * The domain-event-aware version of `getManager()`
     */
    public function getDomainEventAwareManager(
        ObjectManager $objectManager
    ): DomainEventAwareObjectManager;

    /**
     * The domain-event-aware version of `getManagers()`
     *
     * @return array<string,DomainEventAwareObjectManager>
     */
    public function getDomainEventAwareManagers(): array;

    /**
     * The domain-event-aware version of `getManagerForClass()`
     *
     * @param class-string $class
     */
    public function getDomainEventAwareManagerForClass(
        string $class
    ): ?DomainEventAwareObjectManager;

}
