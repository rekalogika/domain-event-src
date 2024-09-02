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

namespace Rekalogika\DomainEvent\Tests\Integration\Event;

use Rekalogika\DomainEvent\Tests\Integration\Model\Entity;

abstract class AbstractEntityDomainEvent
{
    final public function __construct(private readonly Entity $entity) {}

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
