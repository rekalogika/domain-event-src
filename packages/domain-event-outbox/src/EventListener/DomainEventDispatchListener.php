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

namespace Rekalogika\DomainEvent\Outbox\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\DomainEvent\DomainEventAwareManagerRegistry;
use Rekalogika\DomainEvent\Event\DomainEventPreFlushDispatchEvent;
use Rekalogika\DomainEvent\Outbox\Entity\OutboxMessage;
use Rekalogika\DomainEvent\Outbox\Message\MessageRelayStartMessage;
use Rekalogika\DomainEvent\Outbox\MessagePreparerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Listen when a domain event is dispatched, and save it to the outbox table.
 */
class DomainEventDispatchListener implements ResetInterface
{
    /**
     * @var array<string,true>
     */
    public array $managerNames = [];

    public function __construct(
        private readonly MessagePreparerInterface $messagePreparer,
        private readonly MessageBusInterface $messageBus,
        private readonly DomainEventAwareManagerRegistry $managerRegistry,
    ) {}

    #[\Override]
    public function reset(): void
    {
        $this->managerNames = [];
    }

    public function onPreFlushDispatch(DomainEventPreFlushDispatchEvent $event): void
    {
        $domainEvent = $event->getDomainEvent();
        $objectManager = $event->getObjectManager();

        if (!$objectManager instanceof EntityManagerInterface) {
            return;
        }

        $managerName = $this->managerRegistry->getManagerName($objectManager);

        $envelope = new Envelope($domainEvent);
        $envelope = $this->messagePreparer->prepareMessage($envelope);

        if (null === $envelope) {
            return;
        }

        $objectManager->persist(new OutboxMessage($envelope));
        $this->managerNames[$managerName] = true;
    }

    public function onTerminate(): void
    {
        foreach (array_keys($this->managerNames) as $managerName) {
            $this->messageBus->dispatch(new MessageRelayStartMessage($managerName));
        }
    }
}
