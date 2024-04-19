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

namespace Rekalogika\DomainEvent\Tests\Framework\Tests;

final class ResetTest extends DomainEventTestCase
{
    public function testEntityManagerReset(): void
    {
        $entitymanager = static::getEntityManager();
        $entitymanager->reset();
    }

    public function testManagerRegistryResetManager(): void
    {
        $managerRegistry = static::getManagerRegistry();
        $managerRegistry->resetManager();
    }
}
