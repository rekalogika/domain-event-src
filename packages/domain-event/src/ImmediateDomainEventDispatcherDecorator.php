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

namespace Rekalogika\DomainEvent;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Rekalogika\DomainEvent\Event\DomainEventImmediateDispatch;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ImmediateDomainEventDispatcherDecorator implements
    EventDispatcherInterface
{
    public function __construct(
        private EventDispatcherInterface $decorated,
        private PsrEventDispatcherInterface $defaultEventDispatcher,
    ) {
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->decorated->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->decorated->addSubscriber($subscriber);
    }

    public function removeListener(string $eventName, callable $listener): void
    {
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

    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        return $this->decorated->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return $this->decorated->hasListeners($eventName);
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->defaultEventDispatcher->dispatch(new DomainEventImmediateDispatch($event));

        return $this->decorated->dispatch($event, $eventName);
    }
}
