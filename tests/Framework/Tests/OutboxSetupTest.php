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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class OutboxSetupTest extends DomainEventTestCase
{
    /**
     * @dataProvider provideDatabaseSetupCases
     */
    public function testDatabaseSetup(string $id): void
    {
        $managerRegistry = static::getContainer()->get('doctrine');
        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);

        $entityManager = $managerRegistry->getManager($id);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $connection = $entityManager->getConnection();

        $queryBuilder = $connection->createQueryBuilder()
            ->select('name')
            ->from('sqlite_schema')
            ->where('type = ?')
            ->andWhere('name = ?')
            ->setParameter(0, 'table')
            ->setParameter(1, 'rekalogika_event_outbox');

        $queryBuilder->executeStatement();

        $result = $queryBuilder->fetchAssociative();

        self::assertIsArray($result);
        self::assertArrayHasKey('name', $result);
        self::assertEquals('rekalogika_event_outbox', $result['name']);
    }

    /**
     * @return iterable<array-key,array<int,string>>
     */
    public static function provideDatabaseSetupCases(): iterable
    {
        $managerRegistry = static::getContainer()->get('doctrine');
        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);

        foreach ($managerRegistry->getManagers() as $id => $entityManager) {
            yield [$id];
        }
    }
}
