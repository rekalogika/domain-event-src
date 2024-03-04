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

namespace Rekalogika\DomainEvent\Tests\Framework\Tests;

use Rekalogika\DomainEvent\Exception\FlushNotAllowedException;
use Rekalogika\DomainEvent\Tests\Framework\Entity\Book;

final class PreFlushTest extends DomainEventTestCase
{
    public function testFlushInPreFlush(): void
    {
        $book = new Book('title', 'description');
        $book->dummyMethodForFlush();
        static::getEntityManager()->persist($book);
        $this->expectException(FlushNotAllowedException::class);
        static::getEntityManager()->flush();
    }
}
