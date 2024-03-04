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

namespace Rekalogika\DomainEvent\Tests\Framework\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rekalogika\DomainEvent\Tests\Framework\Kernel;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class FrameworkTestCase extends TestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?ContainerInterface $container = null;

    public function setUp(): void
    {
        $kernel = new Kernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    /**
     * @template T of object
     * @param string|class-string<T> $serviceId
     * @return ($serviceId is class-string<T> ? T : object)
     */
    public function get(string $serviceId): object
    {
        try {
            $result = $this->container?->get('test.' . $serviceId);
        } catch (ServiceNotFoundException) {
            /**
             * @psalm-suppress PossiblyNullReference
             * @psalm-suppress MixedAssignment
             */
            $result = $this->container->get($serviceId);
        }


        if (class_exists($serviceId) || interface_exists($serviceId)) {
            $this->assertInstanceOf($serviceId, $result);
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->assertIsObject($result);

        return $result;
    }

    private function doctrineInit(): EntityManagerInterface
    {
        $managerRegistry = $this->get('doctrine');
        $this->assertInstanceOf(ManagerRegistry::class, $managerRegistry);

        $entityManager = $managerRegistry->getManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        return $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager !== null) {
            return $this->entityManager;
        }

        return $this->entityManager = $this->doctrineInit();
    }
}
