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

use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Rekalogika\DomainEvent\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventAwareManagerRegistry;
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
        Constants::EVENT_DISPATCHERS,
        Constants::DOCTRINE_EVENT_LISTENER,
        DomainEventAwareManagerRegistry::class,
        DomainEventAwareEntityManagerInterface::class,
        Constants::IMMEDIATE_DISPATCHER_INSTALLER,
        Constants::REAPER,
    ];

    foreach ($serviceIds as $serviceId) {
        $services
            ->alias('test.' . $serviceId, $serviceId)
            ->public();
    }

    $services
        ->load('Rekalogika\DomainEvent\Tests\Framework\EventListener\\', '../../EventListener/')
        ->load('Rekalogika\DomainEvent\Tests\Framework\Repository\\', '../../Repository/');
};
