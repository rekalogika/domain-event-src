<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests\Service;

use Doctrine\ORM\UnitOfWork;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;
use Rekalogika\DomainEvent\Doctrine\DomainEventEmitterCollectorInterface;

final class DomainEventEmitterCollectorStub implements
    DomainEventEmitterCollectorInterface
{
    /**
     * @var iterable<DomainEventEmitterInterface>
     */
    private iterable $entities;

    public function __construct(DomainEventEmitterInterface ...$entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return iterable<DomainEventEmitterInterface>
     */
    public function collectEntities(UnitOfWork $unitOfWork): iterable
    {
        return $this->entities;
    }
}
