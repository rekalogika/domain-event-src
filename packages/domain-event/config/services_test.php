<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

declare(strict_types=1);

use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Rekalogika\DomainEvent\Tests\Kernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if (!class_exists(Kernel::class)) {
        return;
    }

    $services = $containerConfigurator->services();

    $serviceIds = [
        Constants::EVENT_DISPATCHER_IMMEDIATE,
        Constants::EVENT_DISPATCHER_PRE_FLUSH,
        Constants::EVENT_DISPATCHER_POST_FLUSH,
        DoctrineEventListener::class,
        DomainEventAwareManagerRegistry::class,
        DomainEventAwareEntityManagerInterface::class,
        DomainEventManagerInterface::class,
        ImmediateDomainEventDispatcherInstaller::class,
        DomainEventReaper::class,
    ];

    foreach ($serviceIds as $serviceId) {
        $services->alias('test.' . $serviceId, $serviceId);
    }
};
