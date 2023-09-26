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

namespace Rekalogika\DomainEvent\Doctrine;

use Doctrine\ORM\UnitOfWork;
use Rekalogika\Contracts\DomainEvent\DomainEventEmitterInterface;

/**
 * Collects all entities implementing DomainEventEmitterInterface from the unit
 * of work
 */
interface DomainEventEmitterCollectorInterface
{
    /**
     * @return iterable<DomainEventEmitterInterface>
     */
    public function collectEntities(UnitOfWork $unitOfWork): iterable;
}
