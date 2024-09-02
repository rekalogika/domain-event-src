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

namespace Rekalogika\DomainEvent\Tests\Framework\Event;

final class BookDummyChanged
{
    public function __construct(
        private readonly string $previous,
        private readonly string $now,
    ) {}

    public function getPrevious(): string
    {
        return $this->previous;
    }

    public function getNow(): string
    {
        return $this->now;
    }
}
