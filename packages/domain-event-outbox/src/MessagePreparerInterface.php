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

use Symfony\Component\Messenger\Envelope;

/**
 * Prepares the message before it is saved to the outbox table. Returns null if
 * the message should not be delivered to the outbox.
 */
interface MessagePreparerInterface
{
    public function prepareMessage(Envelope $envelope): ?Envelope;
}
