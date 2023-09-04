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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventManager;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    //
    // event dispatchers
    //

    $services->set(
        Constants::EVENT_DISPATCHER_IMMEDIATE,
        EventDispatcher::class
    )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_IMMEDIATE
        ]);

    $services->set(
        Constants::EVENT_DISPATCHER_PRE_FLUSH,
        EventDispatcher::class
    )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_PRE_FLUSH
        ]);

    $services->set(
        Constants::EVENT_DISPATCHER_POST_FLUSH,
        EventDispatcher::class
    )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_POST_FLUSH
        ]);

    //
    // doctrine
    //

    $services->set(DoctrineEventListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'postPersist',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'preRemove',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'postRemove',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'postUpdate',
        ])
        ->args([
            service(DomainEventManagerInterface::class),
        ]);

    $services->set(
        DomainEventAwareEntityManagerInterface::class,
        DomainEventAwareEntityManager::class
    )
        ->args([
            service('.inner'),
            service(DomainEventManagerInterface::class),
            service(ImmediateDomainEventDispatcherInstaller::class),
        ])
        ->decorate(EntityManagerInterface::class);

    $services->set(DomainEventAwareManagerRegistry::class)
        ->args([
            service('.inner'),
            service(DomainEventManagerInterface::class),
            service(ImmediateDomainEventDispatcherInstaller::class),
        ])
        ->decorate(ManagerRegistry::class);

    //
    // event manager
    //

    $services->set(
        DomainEventManagerInterface::class,
        DomainEventManager::class
    )
        ->args([
            '$defaultEventDispatcher'
            => service(EventDispatcherInterface::class),
            '$postFlushEventDispatcher'
            => service(Constants::EVENT_DISPATCHER_POST_FLUSH),
            '$preFlushEventDispatcher'
            => service(Constants::EVENT_DISPATCHER_PRE_FLUSH),
        ]);

    $services->set(ImmediateDomainEventDispatcherInstaller::class)
        ->args([
            '$eventDispatcher' =>
            service(Constants::EVENT_DISPATCHER_IMMEDIATE),
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.request',
            'method' => 'install',
        ]);

    $services->set(DomainEventReaper::class)
        ->args([
            '$domainEventManager' => service(DomainEventManagerInterface::class),
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.exception',
            'method' => 'onKernelException',
        ]);
};
