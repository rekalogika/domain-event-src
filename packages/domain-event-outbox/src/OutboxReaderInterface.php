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

namespace Rekalogika\DomainEvent\Outbox;

interface OutboxReaderInterface
{
    /**
     * Gets messages from the outbox queue. Starting from the earlier first.
     * Should implicitly start a transaction, that will be committed using
     * `flush()`.
     * 
     * @return array<array-key,object>
     */
    public function getOutboxMessages(int $limit = 100): array;

    /**
     * Removes a message from the outbox queue by its id.
     */
    public function removeOutboxMessageById(int|string $id): void;

    /**
     * Commits the transaction started by getOutboxMessages.
     */
    public function flush(): void;
}
