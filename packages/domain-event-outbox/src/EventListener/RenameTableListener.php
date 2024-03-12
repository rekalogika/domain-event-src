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

namespace Rekalogika\DomainEvent\Outbox\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Rekalogika\DomainEvent\Outbox\Entity\OutboxMessage;

/**
 * Renames the outbox table according to the configuration.
 */
class RenameTableListener
{
    public function __construct(private string $outboxTable)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();

        if ($reflectionClass === null) {
            return;
        }

        if ($reflectionClass->getName() === OutboxMessage::class) {
            $metadata->setPrimaryTable(['name' => $this->outboxTable]);
        }
    }
}
