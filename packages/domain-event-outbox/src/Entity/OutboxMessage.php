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
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Rekalogika\DomainEvent\Outbox\Exception\LogicException;
use Rekalogika\DomainEvent\Outbox\Exception\UnserializeFailureException;
use Symfony\Component\Messenger\Envelope;

#[Entity()]
#[Table(name: 'rekalogika_event_outbox')]
#[Index(fields: ['error'])]
class OutboxMessage
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    private ?int $id = null;

    #[Column(type: "text")]
    private string $event;

    #[Column(type: "boolean", options: ["default" => false])]
    private bool $error = false;

    private ?Envelope $cachedResult = null;

    public function __construct(Envelope $event)
    {
        $this->event = base64_encode(serialize($event));
        $this->cachedResult = $event;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new LogicException('ID is not set');
        }

        return $this->id;
    }

    public function getEvent(): Envelope
    {
        if (null !== $this->cachedResult) {
            return $this->cachedResult;
        }

        $decoded = base64_decode($this->event);
        $result = unserialize($decoded);

        if (false === $result) {
            throw new UnserializeFailureException($this->event);
        }

        if (!$result instanceof Envelope) {
            throw new UnserializeFailureException($this->event);
        }

        return $this->cachedResult = $result;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function setError(bool $error): self
    {
        $this->error = $error;

        return $this;
    }
}
