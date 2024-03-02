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

final readonly class Constants
{
    public const EVENT_DISPATCHER_IMMEDIATE = 'rekalogika.domain_event.dispatcher.immediate';
    public const EVENT_DISPATCHER_PRE_FLUSH = 'rekalogika.domain_event.dispatcher.pre_flush';
    public const EVENT_DISPATCHER_POST_FLUSH = 'rekalogika.domain_event.dispatcher.post_flush';

    private function __construct()
    {
    }
}
