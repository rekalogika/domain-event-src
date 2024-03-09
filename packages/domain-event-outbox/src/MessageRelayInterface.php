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

namespace Rekalogika\DomainEvent\Outbox;

/**
 * Get the messages from the outbox and sends them to the message bus.
 */
interface MessageRelayInterface
{
    /**
     * Relays messages from the outbox to the message bus.
     *
     * @param string $managerName The name of the entity manager to relay
     * messages from.
     * @return int The amount of messages cleared from the outbox, not
     * necessarily sent to the event bus.
     */
    public function relayMessages(string $managerName): int;
}
