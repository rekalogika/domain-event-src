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

namespace Rekalogika\DomainEvent\Tests\Framework\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;
use Rekalogika\DomainEvent\Tests\Framework\Event\BookDummyMethodForFlushCalled;

final class BookDummyMethodForFlushListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AsPreFlushDomainEventListener()]
    public function onDummyMethodCalled(BookDummyMethodForFlushCalled $event): void
    {
        // flush is not allowed in preFlush
        $this->entityManager->flush();
    }
}
