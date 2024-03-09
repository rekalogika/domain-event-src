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

namespace Rekalogika\DomainEvent\Outbox\MessageHandler;

use Rekalogika\DomainEvent\Outbox\Message\MessageRelayStartMessage;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;

/**
 * Starts the message relay after receiving the message
 */
class MessageRelayStartMessageHandler
{
    public function __construct(private MessageRelayInterface $messageRelay)
    {
    }

    public function __invoke(MessageRelayStartMessage $message): void
    {
        do {
            $messagesRelayed = $this->messageRelay->relayMessages($message->getManagerName());
        } while ($messagesRelayed > 0);
    }
}
