<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;

final class IntegrationTest extends TestCase
{
    private ?ContainerInterface $container = null;

    public function setUp(): void
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    public function testServiceWiring(): void
    {
        $serviceIds = [
            DoctrineEventListener::class,
            DomainEventAwareManagerRegistry::class,
            DomainEventAwareEntityManagerInterface::class,
            DomainEventManagerInterface::class,
            ImmediateDomainEventDispatcherInstaller::class,
            DomainEventReaper::class,
        ];

        foreach ($serviceIds as $serviceId) {
            $this->assertInstanceOf($serviceId, $this->container?->get('test.' . $serviceId));
        }
    }

    public function testEventDispatcherWiring(): void
    {
        $serviceIds = [
            Constants::EVENT_DISPATCHER_IMMEDIATE,
            Constants::EVENT_DISPATCHER_PRE_FLUSH,
            Constants::EVENT_DISPATCHER_POST_FLUSH,
        ];

        foreach ($serviceIds as $serviceId) {
            $this->assertInstanceOf(
                EventDispatcherInterface::class,
                $this->container?->get('test.' . $serviceId)
            );
        }
    }
}
