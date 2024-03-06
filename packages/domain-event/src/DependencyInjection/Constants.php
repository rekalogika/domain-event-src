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

namespace Rekalogika\DomainEvent\DependencyInjection;

final class Constants
{
    public const EVENT_DISPATCHERS = 'rekalogika.domain_event.dispatchers';
    public const EVENT_DISPATCHER_IMMEDIATE = 'rekalogika.domain_event.dispatcher.immediate';
    public const EVENT_DISPATCHER_PRE_FLUSH = 'rekalogika.domain_event.dispatcher.pre_flush';
    public const EVENT_DISPATCHER_POST_FLUSH = 'rekalogika.domain_event.dispatcher.post_flush';
    public const IMMEDIATE_EVENT_DISPATCHING_DISPATCHER = 'rekalogika.domain_event.immediate_event_dispatching_dispatcher';
    public const DOCTRINE_EVENT_LISTENER = 'rekalogika.domain_event.doctrine.event_listener';
    public const REAPER = 'rekalogika.domain_event.reaper';
    public const IMMEDIATE_DISPATCHER_INSTALLER = 'rekalogika.domain_event.immediate_dispatcher_installer';
    public const MANAGER_REGISTRY = 'rekalogika.domain_event.doctrine';

    private function __construct()
    {
    }
}
