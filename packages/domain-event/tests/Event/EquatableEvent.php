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

namespace Rekalogika\DomainEvent\Tests\Event;

use Rekalogika\Contracts\DomainEvent\EquatableDomainEventInterface;

final class EquatableEvent extends AbstractEntityDomainEvent implements
    EquatableDomainEventInterface
{
    public function getSignature(): string
    {
        return sha1(serialize($this));
    }
}
