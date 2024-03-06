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

namespace Rekalogika\DomainEvent\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Rekalogika\DomainEvent\Event\AbstractDomainEventDispatchEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventDispatchingDomainEventDispatcher implements
    EventDispatcherInterface
{
    /**
     * @param class-string<AbstractDomainEventDispatchEvent> $eventClass
     */
    public function __construct(
        private EventDispatcherInterface $decorated,
        private PsrEventDispatcherInterface $defaultEventDispatcher,
        private string $eventClass,
    ) {
    }

    // @phpstan-ignore-next-line
    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion @phpstan-ignore-next-line */
        $this->decorated->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->decorated->addSubscriber($subscriber);
    }

    // @phpstan-ignore-next-line
    public function removeListener(string $eventName, callable|array $listener): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion @phpstan-ignore-next-line */
        $this->decorated->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->decorated->removeSubscriber($subscriber);
    }

    public function getListeners(?string $eventName = null): array
    {
        return $this->decorated->getListeners($eventName);
    }

    // @phpstan-ignore-next-line
    public function getListenerPriority(string $eventName, callable|array $listener): ?int
    {
        /** @psalm-suppress MixedArgumentTypeCoercion @phpstan-ignore-next-line */
        return $this->decorated->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return $this->decorated->hasListeners($eventName);
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $eventClass = $this->eventClass;
        $this->defaultEventDispatcher->dispatch(new $eventClass($event));

        return $this->decorated->dispatch($event, $eventName);
    }
}
