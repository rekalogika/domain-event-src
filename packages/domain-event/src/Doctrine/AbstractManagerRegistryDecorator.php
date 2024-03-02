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
    private readonly ManagerRegistry $wrapped;

    public function __construct(ManagerRegistry $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function getDefaultManagerName(): string
    {
        return $this->wrapped->getDefaultManagerName();
    }

    public function getManager(?string $name = null): ObjectManager
    {
        return $this->wrapped->getManager($name);
    }

    public function getManagers(): array
    {
        return $this->wrapped->getManagers();
    }

    public function resetManager(?string $name = null): ObjectManager
    {
        return $this->wrapped->resetManager($name);
    }

    public function getManagerNames(): array
    {
        return $this->wrapped->getManagerNames();
    }

    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): ObjectRepository
    {
        return $this->wrapped->getRepository($persistentObject, $persistentManagerName);
    }

    public function getManagerForClass(string $class): ?ObjectManager
    {
        return $this->wrapped->getManagerForClass($class);
    }

    public function getDefaultConnectionName(): string
    {
        return $this->wrapped->getDefaultConnectionName();
    }

    public function getConnection(?string $name = null): object
    {
        return $this->wrapped->getConnection($name);
    }

    public function getConnections(): array
    {
        return $this->wrapped->getConnections();
    }

    public function getConnectionNames(): array
    {
        return $this->wrapped->getConnectionNames();
    }
}
