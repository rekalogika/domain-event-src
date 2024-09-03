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

use Rekalogika\DomainEvent\Outbox\Message\MessageRelayStartMessage;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Outbox\Stamp\ObjectManagerNameStamp;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;
use Rekalogika\DomainEvent\Tests\Framework\Entity2\Post;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookCreated;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostChanged;
use Rekalogika\DomainEvent\Tests\Framework\Event2\PostCreated;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class OutboxTest extends DomainEventTestCase
{
    private function workWithEntities(): void
    {
        //
        // create & work with entity, then persist it
        //

        // default manager
        $book = new Book('title', 'description');
        $book->setTitle('new title');

        $this->entityManager->persist($book);

        // other manager
        $post = new Post('title', 'description');
        $post->setTitle('new title');
        $this->managerRegistry->getManager('other')->persist($post);

        //
        // flush and clear
        //

        // default manager
        $this->entityManager->flush();
        $this->entityManager->clear();

        // other manager
        $this->managerRegistry->getManager('other')->flush();
        $this->managerRegistry->getManager('other')->clear();
    }

    public function testOutboxQueuing(): void
    {
        $this->workWithEntities();

        // get outbox reader factory
        $outboxReaderFactory = static::getContainer()->get(OutboxReaderFactoryInterface::class);
        self::assertInstanceOf(OutboxReaderFactoryInterface::class, $outboxReaderFactory);

        // default manager
        $outboxReader = $outboxReaderFactory->createOutboxReader('default');
        $messages = $outboxReader->getOutboxMessages(100);
        $messages = array_values($messages instanceof \Traversable ? iterator_to_array($messages) : $messages);
        self::assertInstanceOf(Envelope::class, $messages[0]);
        self::assertInstanceOf(BookCreated::class, $messages[0]->getMessage());
        self::assertInstanceOf(Envelope::class, $messages[1]);
        self::assertInstanceOf(BookChanged::class, $messages[1]->getMessage());

        // other manager
        $outboxReader = $outboxReaderFactory->createOutboxReader('other');
        $messages = $outboxReader->getOutboxMessages(100);
        $messages = array_values($messages instanceof \Traversable ? iterator_to_array($messages) : $messages);
        self::assertInstanceOf(Envelope::class, $messages[0]);
        self::assertInstanceOf(PostCreated::class, $messages[0]->getMessage());
        self::assertInstanceOf(Envelope::class, $messages[1]);
        self::assertInstanceOf(PostChanged::class, $messages[1]->getMessage());
    }

    private function assertMessagesInTransport(): void
    {
        // get transport
        $transport = $this->getContainer()->get('messenger.transport.async');
        self::assertInstanceOf(InMemoryTransport::class, $transport);

        // get sent messages
        $messages = $transport->getSent();
        self::assertCount(2, $messages);

        // check first message
        $first = $messages[0];
        self::assertInstanceOf(Envelope::class, $first);
        self::assertInstanceOf(BookChanged::class, $first->getMessage());

        $busNameStamp = $first->last(BusNameStamp::class);
        self::assertInstanceOf(BusNameStamp::class, $busNameStamp);
        self::assertEquals('rekalogika.domain_event.bus', $busNameStamp->getBusName());

        $sentStamp = $first->last(SentStamp::class);
        self::assertInstanceOf(SentStamp::class, $sentStamp);
        self::assertEquals('async', $sentStamp->getSenderAlias());

        $objectManagerNameStamp = $first->last(ObjectManagerNameStamp::class);
        self::assertInstanceOf(ObjectManagerNameStamp::class, $objectManagerNameStamp);
        self::assertEquals('default', $objectManagerNameStamp->getObjectManagerName());

        // check second message
        $second = $messages[1];
        self::assertInstanceOf(Envelope::class, $second);
        self::assertInstanceOf(PostChanged::class, $second->getMessage());

        $busNameStamp = $second->last(BusNameStamp::class);
        self::assertInstanceOf(BusNameStamp::class, $busNameStamp);
        self::assertEquals('rekalogika.domain_event.bus', $busNameStamp->getBusName());

        $sentStamp = $second->last(SentStamp::class);
        self::assertInstanceOf(SentStamp::class, $sentStamp);
        self::assertEquals('async', $sentStamp->getSenderAlias());

        $objectManagerNameStamp = $second->last(ObjectManagerNameStamp::class);
        self::assertInstanceOf(ObjectManagerNameStamp::class, $objectManagerNameStamp);
        self::assertEquals('other', $objectManagerNameStamp->getObjectManagerName());
    }

    public function testMessageRelay(): void
    {
        $this->workWithEntities();

        // get message relay
        $messageRelay = static::getContainer()->get(MessageRelayInterface::class);
        self::assertInstanceOf(MessageRelayInterface::class, $messageRelay);

        // relay messages
        $messageRelay->relayMessages('default');
        $messageRelay->relayMessages('other');

        // assert messages in transport
        $this->assertMessagesInTransport();
    }

    public function testMessageRelayHandler(): void
    {
        $this->workWithEntities();

        // get message bus
        $messageBus = static::getContainer()->get(MessageBusInterface::class);
        self::assertInstanceOf(MessageBusInterface::class, $messageBus);

        // tell to relay messages
        $messageBus->dispatch(new MessageRelayStartMessage('default'));
        $messageBus->dispatch(new MessageRelayStartMessage('other'));

        // assert messages in transport
        $this->assertMessagesInTransport();
    }

    public function testEquatableMessage(): void
    {
        //
        // create & work with entity, then persist it
        //

        // default manager
        $book = new Book('title', 'description');

        $book->setTitle('new title');

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        $book->setTitle('new title2');
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // other manager
        $post = new Post('title', 'description');

        $post->setTitle('new title');
        $this->managerRegistry->getManager('other')->persist($post);
        $this->managerRegistry->getManager('other')->flush();

        $post->setTitle('new title2');
        $this->managerRegistry->getManager('other')->persist($post);
        $this->managerRegistry->getManager('other')->flush();

        // get message relay
        $messageRelay = static::getContainer()->get(MessageRelayInterface::class);
        self::assertInstanceOf(MessageRelayInterface::class, $messageRelay);

        // relay messages
        $messageRelay->relayMessages('default');
        $messageRelay->relayMessages('other');

        // assert messages in transport
        $this->assertMessagesInTransport();
    }
}
