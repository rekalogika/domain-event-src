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

namespace Rekalogika\DomainEvent\Outbox\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Rekalogika\DomainEvent\Outbox\OutboxReaderFactoryInterface;
use Rekalogika\DomainEvent\Outbox\OutboxReaderInterface;

class OutboxReaderFactory implements OutboxReaderFactoryInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function createOutboxReader(string $managerName): OutboxReaderInterface
    {
        $manager = $this->managerRegistry->getManager($managerName);

        if ($manager instanceof EntityManagerInterface) {
            return new EntityManagerOutboxReader($manager);
        }

        throw new \InvalidArgumentException(sprintf('Object manager with name "%s" is an instance of "%s", but it is unsupported', $managerName, \get_class($manager)));
    }
}
