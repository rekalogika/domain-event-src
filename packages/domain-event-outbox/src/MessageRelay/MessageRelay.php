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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;
use Rekalogika\DomainEvent\Outbox\Entity\ErrorEvent;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Outbox\Stamp\ObjectManagerNameStamp;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessageRelay implements MessageRelayInterface
{
    public function __construct(
        private readonly OutboxReaderFactoryInterface $outboxReaderFactory,
        private readonly HandlersLocatorInterface $handlersLocator,
        private readonly MessageBusInterface $domainEventBus,
        private readonly LockFactory $lockFactory,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly int $limit = 100
    ) {
    }

    public function relayMessages(string $managerName): int
    {
        $lock = $this->lockFactory->createLock(self::class . '-' . $managerName);

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

                if ($message instanceof ErrorEvent) {
                    $outboxReader->flagError($id);
                    $this->logger->error('Error event {id} found in the outbox, you should inspect it manually', ['id' => $id]);
                    continue;
                }

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
                        ->with(new ObjectManagerNameStamp($managerName));

                    $this->domainEventBus->dispatch($envelope);

                    $this->logger->info('Message relayed, message id: {id}, class: {class}', [
                        'id' => $id,
                        'class' => $message::class
                    ]);
                } else {
                    $this->logger->info('Message relaying skipped because no handler is found, message id: {id}, class: {class}', [
                        'id' => $id,
                        'class' => $message::class
                    ]);
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
        $handlers = $this->handlersLocator->getHandlers($envelope);
        $handlers = $handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers;

        return \count($handlers) > 0;
    }
}
