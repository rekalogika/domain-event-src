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

namespace Rekalogika\DomainEvent\Exception;

class FlushNotAllowedException extends DomainEventException
{
    public function __construct()
    {
        parent::__construct('"flush()" is not allowed inside a pre-flush domain event listener.');
    }
}
