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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Rekalogika\DomainEvent\Tests\Framework\Kernel;
use Rekalogika\DomainEvent\Tests\Integration\Factory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if (!class_exists(Kernel::class)) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(EntityManagerInterface::class)
        ->factory([Factory::class, 'mockEntityManager']);

    $services
        ->set(ManagerRegistry::class)
        ->factory([Factory::class, 'mockManagerRegistry']);

    $services
        ->set(EventDispatcherInterface::class)
        ->factory([Factory::class, 'mockEventDispatcher']);

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
        $services
            ->alias('test.' . $serviceId, $serviceId)
            ->public();
    }
};
