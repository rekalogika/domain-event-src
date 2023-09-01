<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests\Event;

use Rekalogika\DomainEvent\Tests\Model\Entity;

abstract class AbstractEntityDomainEvent
{
    final public function __construct(private Entity $entity)
    {
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
