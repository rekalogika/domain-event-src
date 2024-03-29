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
use Rekalogika\DomainEvent\Outbox\Entity\ErrorEvent;
use Rekalogika\DomainEvent\Outbox\Entity\OutboxMessage;
use Rekalogika\DomainEvent\Outbox\Exception\UnserializeFailureException;
use Rekalogika\DomainEvent\Outbox\OutboxReaderInterface;
use Symfony\Component\Messenger\Envelope;

class EntityManagerOutboxReader implements OutboxReaderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getOutboxMessages(int $limit): iterable
    {
        $this->entityManager->beginTransaction();

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->from(OutboxMessage::class, 'o')
            ->select('o')
            ->where('o.error = false')
            ->orderBy('o.id', 'ASC')
            ->setMaxResults($limit);

        $result = $queryBuilder->getQuery()->getResult();
        \assert(\is_array($result));

        foreach ($result as $row) {
            \assert($row instanceof OutboxMessage);

            $id = $row->getId();

            try {
                $event = $row->getEvent();
                yield $id => $event;
            } catch (UnserializeFailureException) {
                yield $id => new Envelope(new ErrorEvent());
            }
        }
    }

    public function removeOutboxMessageById(int|string $id): void
    {
        /** @var OutboxMessage */
        $object = $this->entityManager->getReference(OutboxMessage::class, $id);
        $this->entityManager->remove($object);
    }

    public function flagError(int|string $id): void
    {
        /** @var OutboxMessage */
        $object = $this->entityManager->getReference(OutboxMessage::class, $id);
        $object->setError(true);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }
}
