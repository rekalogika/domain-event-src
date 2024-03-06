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

use Doctrine\Persistence\ObjectManager;

/**
 * Takes an object manager instance, and returns our decorated version of it.
 */
interface ObjectManagerDecoratorResolverInterface
{
    public function getDecoratedObjectManager(
        ObjectManager $objectManager
    ): ObjectManager;
}
