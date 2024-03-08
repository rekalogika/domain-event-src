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

namespace Rekalogika\DomainEvent\Outbox\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Rekalogika\DomainEvent\Outbox\Exception\LogicException;
use Rekalogika\DomainEvent\Outbox\Exception\UnserializeFailureException;

#[Entity()]
#[Table(name: 'rekalogika_event_outbox')]
class OutgoingEvent
{
    #[Id]
    #[Column(type: "integer")]
    #[GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[Column(type: "text")]
    private string $event;

    public function __construct(object $event)
    {
        $this->event = serialize($event);
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new LogicException('ID is not set');
        }

        return $this->id;
    }

    public function getEvent(): object
    {
        $result = unserialize($this->event);

        if (false === $result) {
            throw new UnserializeFailureException($this->event);
        }

        if (!\is_object($result)) {
            throw new UnserializeFailureException($this->event);
        }

        return $result;
    }
}
