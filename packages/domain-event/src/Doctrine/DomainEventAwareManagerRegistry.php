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

namespace Rekalogika\DomainEvent\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Rekalogika\DomainEvent\EventDispatcher\EventDispatchers;
use Symfony\Contracts\Service\ResetInterface;

final class DomainEventAwareManagerRegistry extends AbstractManagerRegistryDecorator implements ResetInterface
{
    public function __construct(
        private ManagerRegistry $wrapped,
        private EventDispatchers $eventDispatchers,
    ) {
        parent::__construct($wrapped);
    }

    public function reset(): void
    {
        if ($this->wrapped instanceof ResetInterface) {
            $this->wrapped->reset();
        }
    }

    /**
     * @return ($manager is null ? null : ObjectManager)
     */
    private function decorate(?ObjectManager $manager): ?ObjectManager
    {
        if ($manager instanceof DomainEventAwareEntityManager) {
            return $manager;
        }

        if (null === $manager) {
            return null;
        }

        if ($manager instanceof EntityManagerInterface) {
            return new DomainEventAwareEntityManager(
                wrapped: $manager,
                eventDispatchers: $this->eventDispatchers,
            );
        }
        return $manager;
    }

    public function getManager(?string $name = null): ObjectManager
    {
        $manager = parent::getManager($name);

        return $this->decorate($manager);
    }

    /**
     * @return array<string,ObjectManager>
     */
    public function getManagers(): array
    {
        $managers = parent::getManagers();

        foreach ($managers as $name => $manager) {
            $managers[$name] = $this->decorate($manager);
        }

        return $managers;
    }

    public function resetManager(?string $name = null): ObjectManager
    {
        $manager = parent::resetManager($name);

        return $this->decorate($manager);
    }

    public function getManagerForClass(string $class): ?ObjectManager
    {
        $manager = parent::getManagerForClass($class);

        return $this->decorate($manager);
    }

    public function getRepository(
        string $persistentObject,
        ?string $persistentManagerName = null
    ): ObjectRepository {
        return $this
            ->selectManager($persistentObject, $persistentManagerName)
            ->getRepository($persistentObject);
    }

    /**
     * @param class-string $persistentObject
     * */
    private function selectManager(
        string $persistentObject,
        ?string $persistentManagerName = null
    ): ObjectManager {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObject) ?? $this->getManager();
    }
}
