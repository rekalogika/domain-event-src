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

use Psr\EventDispatcher\EventDispatcherInterface;
use Rekalogika\DomainEvent\Constants;
use Rekalogika\DomainEvent\Contracts\DomainEventAwareEntityManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DoctrineEventListener;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\DomainEventReaper;
use Rekalogika\DomainEvent\Event\DomainEventImmediateDispatchEvent;
use Rekalogika\DomainEvent\Event\DomainEventPostFlushDispatchEvent;
use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\EventDispatchers;
use Rekalogika\DomainEvent\EventDispatchingDomainEventDispatcher;
use Rekalogika\DomainEvent\ImmediateDomainEventDispatcherInstaller;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    //
    // event dispatchers
    //

    $services
        ->set(EventDispatchers::class)
        ->args([
            '$defaultEventDispatcher' => service(EventDispatcherInterface::class),
            '$immediateEventDispatcher' => service(Constants::EVENT_DISPATCHER_IMMEDIATE),
            '$preFlushEventDispatcher' => service(Constants::EVENT_DISPATCHER_PRE_FLUSH),
            '$postFlushEventDispatcher' => service(Constants::EVENT_DISPATCHER_POST_FLUSH),
        ]);

    //
    // individual event dispatchers
    //

    $services
        ->set(
            Constants::EVENT_DISPATCHER_IMMEDIATE,
            EventDispatcher::class
        )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_IMMEDIATE
        ]);

    $services
        ->set(
            Constants::EVENT_DISPATCHER_PRE_FLUSH,
            EventDispatcher::class
        )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_PRE_FLUSH
        ]);

    $services
        ->set(
            Constants::EVENT_DISPATCHER_POST_FLUSH,
            EventDispatcher::class
        )
        ->tag('event_dispatcher.dispatcher', [
            'name' => Constants::EVENT_DISPATCHER_POST_FLUSH
        ]);

    //
    // event dispatcher decorator
    //

    $services
        ->set(EventDispatchingDomainEventDispatcher::class)
        ->args([
            '$decorated' => service('.inner'),
            '$defaultEventDispatcher' => service(EventDispatcherInterface::class),
            '$eventClass' => DomainEventImmediateDispatchEvent::class,
        ])
        ->decorate(Constants::EVENT_DISPATCHER_IMMEDIATE);

    $services
        ->set(EventDispatchingDomainEventDispatcher::class)
        ->args([
            '$decorated' => service('.inner'),
            '$defaultEventDispatcher' => service(EventDispatcherInterface::class),
            '$eventClass' => DomainEventPreFlushDispatchEvent::class,
        ])
        ->decorate(Constants::EVENT_DISPATCHER_PRE_FLUSH);

    $services
        ->set(EventDispatchingDomainEventDispatcher::class)
        ->args([
            '$decorated' => service('.inner'),
            '$defaultEventDispatcher' => service(EventDispatcherInterface::class),
            '$eventClass' => DomainEventPostFlushDispatchEvent::class,
        ])
        ->decorate(Constants::EVENT_DISPATCHER_POST_FLUSH);

    //
    // doctrine
    //

    $services
        ->set(DoctrineEventListener::class)
        ->args([
            service(DomainEventAwareEntityManagerInterface::class),
        ])
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
        ]);

    $services->alias(
        DomainEventAwareEntityManagerInterface::class,
        'doctrine.orm.entity_manager'
    );

    $services
        ->set(DomainEventAwareManagerRegistry::class)
        ->args([
            '$wrapped' => service('.inner'),
            '$eventDispatchers' => service(EventDispatchers::class),
        ])
        ->decorate('doctrine')
        ->tag('kernel.reset', [
            'method' => 'reset',
        ]);

    //
    // error handler / domain event reaper
    //

    $services
        ->set(DomainEventReaper::class)
        ->args([
            '$entityManagers' => tagged_iterator('rekalogika.domain_event.entity_manager')
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.exception',
            'method' => 'onKernelException',
        ])
        ->tag('kernel.event_listener', [
            'event' => 'console.error',
            'method' => 'onKernelException',
        ]);

    //
    // immediate event dispatcher installer
    //

    $services
        ->set(ImmediateDomainEventDispatcherInstaller::class)
        ->args([
            '$eventDispatcher' =>
            service(Constants::EVENT_DISPATCHER_IMMEDIATE),
        ])
        ->public();
};
