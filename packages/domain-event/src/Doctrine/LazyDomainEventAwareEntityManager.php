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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * Decorates entity manager so it dispatches domain events after flush.
 */
final class LazyDomainEventAwareEntityManager extends DomainEventAwareEntityManager implements LazyObjectInterface
{
}
