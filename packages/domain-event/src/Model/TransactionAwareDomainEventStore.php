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

namespace Rekalogika\DomainEvent\Model;

class TransactionAwareDomainEventStore extends DomainEventStore
{
    private ?self $transactionStore = null;

    #[\Override]
    public function add(object|iterable $event): void
    {
        if ($this->transactionStore !== null) {
            $this->transactionStore->add($event);
        } else {
            parent::add($event);
        }
    }

    #[\Override]
    public function clear(): void
    {
        $this->transactionStore = null;
        parent::clear();
    }

    #[\Override]
    public function count(): int
    {
        $countFromTransaction = $this->transactionStore !== null ? parent::count() : 0;

        return parent::count() + $countFromTransaction;
    }

    public function beginTransaction(): void
    {
        if ($this->transactionStore !== null) {
            $this->transactionStore->beginTransaction();
        } else {
            $this->transactionStore = new self();
        }
    }

    /**
     * @return bool false if there is no transaction in progress
     */
    public function commit(): bool
    {
        if ($this->transactionStore === null) {
            return false;
        }

        $result = $this->transactionStore->commit();

        if ($result === false) {
            $transactionStore = $this->transactionStore;
            $this->transactionStore = null;
            $this->add($transactionStore->pop());
        }

        return true;
    }

    /**
     * @return bool false if there is no transaction in progress
     */
    public function rollback(): bool
    {
        if ($this->transactionStore === null) {
            return false;
        }

        $result = $this->transactionStore->rollback();

        if ($result === false) {
            $this->transactionStore = null;
        }

        return true;
    }
}
