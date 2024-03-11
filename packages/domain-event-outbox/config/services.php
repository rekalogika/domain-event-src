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

use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\Outbox\Command\MessageRelayCommand;
use Rekalogika\DomainEvent\Outbox\Doctrine\OutboxReaderFactory;
use Rekalogika\DomainEvent\Outbox\EventListener\DomainEventDispatchListener;
use Rekalogika\DomainEvent\Outbox\EventListener\RenameTableListener;
use Rekalogika\DomainEvent\Outbox\Message\MessageRelayStartMessage;
use Rekalogika\DomainEvent\Outbox\MessageHandler\MessageRelayStartMessageHandler;
use Rekalogika\DomainEvent\Outbox\MessagePreparer\ChainMessagePreparer;
use Rekalogika\DomainEvent\Outbox\MessagePreparer\UserIdentifierMessagePreparer;
use Rekalogika\DomainEvent\Outbox\MessageRelay\MessageRelay;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(
            OutboxReaderFactoryInterface::class,
            OutboxReaderFactory::class
        )
        ->args([
            '$managerRegistry' => service('doctrine'),
        ]);

    $services
        ->set(
            'rekalogika.domain_event.outbox.dispatch_listener',
            DomainEventDispatchListener::class
        )
        ->args([
            '$messagePreparer' => service('rekalogika.domain_event.outbox.message_preparer.chain'),
            '$messageBus' => service(MessageBusInterface::class),
            '$managerRegistry' => service('doctrine'),
        ])
        ->tag('kernel.event_listener', [
            'event' => DomainEventPreFlushDispatchEvent::class,
            'method' => 'onPreFlushDispatch'
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.terminate',
            'method' => 'onTerminate'
        ])
        ->tag('kernel.reset', ['method' => 'reset']);;

    $services
        ->set(
            'rekalogika.domain_event.outbox.message_preparer.chain',
            ChainMessagePreparer::class
        )
        ->args([
            tagged_iterator('rekalogika.domain_event.outbox.message_preparer')
        ]);

    $services
        ->set(
            'rekalogika.domain_event.outbox.message_preparer.user_identifier',
            UserIdentifierMessagePreparer::class
        )
        ->args([
            '$tokenStorage' => service('security.token_storage'),
        ])
        ->tag('rekalogika.domain_event.outbox.message_preparer');

    $services
        ->set(
            MessageRelayInterface::class,
            MessageRelay::class
        )
        ->args([
            '$outboxReaderFactory' => service(OutboxReaderFactoryInterface::class),
            '$domainEventBus' => service('rekalogika.domain_event.bus'),
            '$handlersLocator' => service('rekalogika.domain_event.bus.messenger.handlers_locator'),
            '$lockFactory' => service(LockFactory::class),
            '$logger' => service('logger'),
            '$limit' => 100,
        ]);

    $services
        ->set(
            MessageRelayStartMessageHandler::class
        )
        ->args([
            '$messageRelay' => service(MessageRelayInterface::class),
        ])
        ->tag('messenger.message_handler', [
            'handles' => MessageRelayStartMessage::class,
        ]);

    $services
        ->set(
            'rekalogika.domain_event.outbox.command.relay_messages',
            MessageRelayCommand::class
        )
        ->args([
            '$defaultManagerName' => '%doctrine.default_entity_manager%',
            '$messageRelay' => service(MessageRelayInterface::class),
        ])
        ->tag('console.command', [
            'command' => 'rekalogika:domain-event:relay',
            'description' => 'Reads messages from the outbox and relays them to the message bus.',
        ]);

    $services
        ->set(
            'rekalogika.domain_event.outbox.rename_table',
            RenameTableListener::class
        )
        ->args([
            '$outboxTable' => '%rekalogika.domain_event.outbox.outbox_table%',
        ])
        ->tag('doctrine.event_listener', [
            'event' => 'loadClassMetadata',
            'lazy' => true,
        ]);
};
