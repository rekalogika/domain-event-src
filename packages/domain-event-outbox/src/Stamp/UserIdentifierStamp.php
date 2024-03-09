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

namespace Rekalogika\DomainEvent\Outbox\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class UserIdentifierStamp implements StampInterface
{
    public function __construct(private readonly string $userIdentifier)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
