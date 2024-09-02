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

namespace Rekalogika\DomainEvent\Outbox\Message;

/**
 * Delivered to the message bus to instruct its handler to start the message
 * relay
 */
class MessageRelayStartMessage
{
    public function __construct(private readonly string $managerName) {}

    public function getManagerName(): string
    {
        return $this->managerName;
    }
}
