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

use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;

final class IntegrationTest extends DomainEventTestCase
{
    public function testServiceWiring(): void
    {
        $serviceIds = [
            DoctrineEventListener::class,
            DomainEventAwareManagerRegistry::class,
            DomainEventAwareEntityManagerInterface::class,
            ImmediateDomainEventDispatcherInstaller::class,
            DomainEventReaper::class,
        ];

        foreach ($serviceIds as $serviceId) {
            $this->assertInstanceOf($serviceId, static::getContainer()->get('test.' . $serviceId));
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
                static::getContainer()->get('test.' . $serviceId)
            );
        }
    }
}
