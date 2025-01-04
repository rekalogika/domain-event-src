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

use Doctrine\Persistence\ManagerRegistry;
use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;

/**
 * Relay messages from all managers
 */
final class MessageRelayAll
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly MessageRelayInterface $messageRelay,
    ) {}

    public function relayAll(): void
    {
        $managerNames = $this->managerRegistry->getManagerNames();

        foreach ($managerNames as $managerName => $serviceId) {
            do {
                $messagesRelayed = $this->messageRelay->relayMessages($managerName);
            } while ($messagesRelayed > 0);
        }
    }
}
