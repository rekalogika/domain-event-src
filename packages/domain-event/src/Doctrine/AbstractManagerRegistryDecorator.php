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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

abstract class AbstractManagerRegistryDecorator implements ManagerRegistry
{
    public function __construct(private readonly ManagerRegistry $wrapped) {}

    #[\Override]
    public function getDefaultManagerName(): string
    {
        return $this->wrapped->getDefaultManagerName();
    }

    #[\Override]
    public function getManager(?string $name = null): ObjectManager
    {
        return $this->wrapped->getManager($name);
    }

    #[\Override]
    public function getManagers(): array
    {
        return $this->wrapped->getManagers();
    }

    #[\Override]
    public function resetManager(?string $name = null): ObjectManager
    {
        return $this->wrapped->resetManager($name);
    }

    #[\Override]
    public function getManagerNames(): array
    {
        return $this->wrapped->getManagerNames();
    }

    #[\Override]
    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): ObjectRepository
    {
        return $this->wrapped->getRepository($persistentObject, $persistentManagerName);
    }

    #[\Override]
    public function getManagerForClass(string $class): ?ObjectManager
    {
        return $this->wrapped->getManagerForClass($class);
    }

    #[\Override]
    public function getDefaultConnectionName(): string
    {
        return $this->wrapped->getDefaultConnectionName();
    }

    #[\Override]
    public function getConnection(?string $name = null): object
    {
        return $this->wrapped->getConnection($name);
    }

    #[\Override]
    public function getConnections(): array
    {
        return $this->wrapped->getConnections();
    }

    #[\Override]
    public function getConnectionNames(): array
    {
        return $this->wrapped->getConnectionNames();
    }
}
