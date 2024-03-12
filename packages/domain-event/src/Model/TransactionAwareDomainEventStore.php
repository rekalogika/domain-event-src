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

use Rekalogika\DomainEvent\Exception\InvalidOperationException;

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
    public function pop(): iterable
    {
        if ($this->transactionStore !== null) {
            throw new InvalidOperationException('Cannot pop, transaction is in progress');
        }

        return parent::pop();
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        if ($this->transactionStore !== null) {
            throw new InvalidOperationException('Cannot iterate, transaction is in progress');
        }

        return parent::getIterator();
    }

    #[\Override]
    public function count(): int
    {
        if ($this->transactionStore !== null) {
            $countFromTransaction = parent::count();
        } else {
            $countFromTransaction = 0;
        }

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

    public function commit(): void
    {
        if ($this->transactionStore === null) {
            throw new InvalidOperationException('Cannot commit, no transaction in progress');
        }

        try {
            $this->transactionStore->commit();
        } catch (InvalidOperationException) {
            $transactionStore = $this->transactionStore;
            $this->transactionStore = null;
            $this->add($transactionStore->pop());
        }
    }

    public function rollback(): void
    {
        if ($this->transactionStore === null) {
            throw new InvalidOperationException('Cannot rollback, no transaction in progress');
        }

        try {
            $this->transactionStore->rollback();
        } catch (InvalidOperationException) {
            $this->transactionStore = null;
        }
    }
}
