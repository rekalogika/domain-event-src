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

namespace Rekalogika\Contracts\DomainEvent;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatches domain events immediately.
 */
final class DomainEventImmediateDispatcher
{
    private static ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * Disallow instantiation
     */
    private function __construct()
    {
    }

    /**
     * Called at the beginning of the request to install the event dispatcher.
     */
    public static function install(EventDispatcherInterface $eventDispatcher): void
    {
        self::$eventDispatcher = $eventDispatcher;
    }

    /**
     * Dispatches an event using the installed event dispatcher
     *
     * @template T of object
     * @param T $event
     * @return T
     */
    public static function dispatch(object $event): object
    {
        if (!self::$eventDispatcher) {
            throw new \RuntimeException('ImmediateDomainEventDispatcher has not been initialized');
        }

        /** @var T */
        $result = self::$eventDispatcher->dispatch($event);

        return $result;
    }
}
