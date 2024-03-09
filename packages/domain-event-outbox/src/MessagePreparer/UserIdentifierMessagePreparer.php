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
use Rekalogika\DomainEvent\Outbox\Stamp\UserIdentifierStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Prepares the message before it is saved to the outbox table.
 */
class UserIdentifierMessagePreparer implements MessagePreparerInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function prepareMessage(Envelope $envelope): ?Envelope
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (null !== $user) {
            $envelope = $envelope->with(new UserIdentifierStamp($user->getUserIdentifier()));
        }

        return $envelope;
    }
}
