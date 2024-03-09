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

namespace Rekalogika\DomainEvent\Outbox\Exception;

class UnserializeFailureException extends RuntimeException
{
    public function __construct(string $serializedText)
    {
        parent::__construct(sprintf('Failed to unserialize serialized event object: "%s"', $serializedText));
    }
}
