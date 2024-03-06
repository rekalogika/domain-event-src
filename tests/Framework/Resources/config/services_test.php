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

use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $serviceIds = [
        Constants::EVENT_DISPATCHER_IMMEDIATE,
        Constants::EVENT_DISPATCHER_PRE_FLUSH,
        Constants::EVENT_DISPATCHER_POST_FLUSH,
        DoctrineEventListener::class,
        DomainEventAwareManagerRegistry::class,
        DomainEventAwareEntityManagerInterface::class,
        ImmediateDomainEventDispatcherInstaller::class,
        DomainEventReaper::class,
    ];

    foreach ($serviceIds as $serviceId) {
        $services
            ->alias('test.' . $serviceId, $serviceId)
            ->public();
    }

    $services
        ->load('Rekalogika\DomainEvent\Tests\Framework\EventListener\\', '../../EventListener/');
};
