<?php

/*
 * This file is part of rekalogika/domain-event-contracts package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Contracts\DomainEvent;

/**
 * Provides a method that can be used to determine whether two events should be
 * considered equal.
 */
interface EquatableDomainEventInterface
{
    public function getSignature(): string;
}
