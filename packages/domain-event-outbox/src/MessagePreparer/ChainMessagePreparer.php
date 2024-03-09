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

namespace Rekalogika\DomainEvent\Outbox\MessagePreparer;

use Rekalogika\DomainEvent\Outbox\MessagePreparerInterface;
use Symfony\Component\Messenger\Envelope;

/**
 * Prepares the message before it is saved to the outbox table.
 */
class ChainMessagePreparer implements MessagePreparerInterface
{
    /**
     * @param iterable<MessagePreparerInterface> $messagePreparers
     */
    public function __construct(private iterable $messagePreparers)
    {
    }

    public function prepareMessage(Envelope $envelope): ?Envelope
    {
        foreach ($this->messagePreparers as $messagePreparer) {
            $envelope = $messagePreparer->prepareMessage($envelope);

            if (null === $envelope) {
                return null;
            }
        }

        return $envelope;
    }
}
