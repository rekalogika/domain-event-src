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

namespace Rekalogika\DomainEvent\Outbox\MessageRelay;

use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Outbox\Stamp\ObjectManagerNameStamp;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

final readonly class MessageRelay implements MessageRelayInterface
{
    public function __construct(
        private OutboxReaderFactoryInterface $outboxReaderFactory,
        private HandlersLocatorInterface $handlersLocator,
        private MessageBusInterface $domainEventBus,
        private LockFactory $lockFactory,
        private int $limit = 100
    ) {
    }

    public function relayMessages(string $managerName): int
    {
        $lock = $this->lockFactory->createLock(__CLASS__ . '-' . $managerName);

        if (!$lock->acquire()) {
            return 0;
        }

        $outboxReader = $this->outboxReaderFactory->createOutboxReader($managerName);

        try {
            $messages = $outboxReader->getOutboxMessages($this->limit);
            /** @var array<string,true> */
            $messageSignatures = [];
            $i = 0;

            foreach ($messages as $id => $envelope) {
                $i++;
                
                $message = $envelope->getMessage();

                if ($message instanceof EquatableDomainEventInterface) {
                    $signature = $message->getSignature();

                    if (isset($messageSignatures[$signature])) {
                        $outboxReader->removeOutboxMessageById($id);
                        continue;
                    }

                    $messageSignatures[$signature] = true;
                }

                if ($this->messageHasHandlers($envelope)) {
                    $envelope = $envelope
                        ->with(new TransportNamesStamp(['rekalogika.domain_event.transport']))
                        ->with(new ObjectManagerNameStamp($managerName));

                    $this->domainEventBus->dispatch($envelope);
                }

                $outboxReader->removeOutboxMessageById($id);
            }

            return $i;
        } finally {
            $outboxReader->flush();
            $lock->release();
        }
    }

    private function messageHasHandlers(Envelope $envelope): bool
    {
        /** @psalm-suppress InvalidArgument */
        return count(iterator_to_array($this->handlersLocator->getHandlers($envelope))) > 0;
    }
}
