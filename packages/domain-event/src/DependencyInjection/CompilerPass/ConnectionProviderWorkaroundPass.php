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

namespace Rekalogika\DomainEvent\DependencyInjection\CompilerPass;

use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Workaround for this error:
 *
 * [ERROR] Invalid definition for service
 * "doctrine.manager_registry_aware_connection_provider": argument 1 of
 * "Doctrine\Bundle\DoctrineBundle\Dbal\ManagerRegistryAwareConnectionProvider::__construct()"
 * accepts "Doctrine\Persistence\AbstractManagerRegistry",
 * "Rekalogika\DomainEvent\Doctrine\DomainEventAwareManagerRegistryImplementation"
 * passed.
 *
 * The connection provider only needs connections, so the real manager registry
 * is sufficient.
 *
 * @internal
 */
final class ConnectionProviderWorkaroundPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        try {
            $doctrine = $container->getDefinition(Constants::REAL_MANAGER_REGISTRY);

            $connectionProvider = $container->getDefinition('doctrine.manager_registry_aware_connection_provider');
            $connectionProvider->setArgument(0, $doctrine);
        } catch (ServiceNotFoundException) {
            // ignore
        }
    }
}
