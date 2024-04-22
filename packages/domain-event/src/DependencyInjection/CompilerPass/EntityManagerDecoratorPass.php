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
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates entity managers manually instead of relying on DI's decoration.
 * 
 * `ManagerRegistry::resetManager()` relies on the fact that entity managers are
 * lazy. However, when we decorate entity managers, the original entity manager
 * won't lazy anymore. This causes `resetManager()` to fail. the workaround is to
 * manually decorate entity managers.
 * 
 * @internal
 */
final class EntityManagerDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        \assert(\is_array($entityManagers));

        $eventDispatchers = $container->getDefinition(Constants::EVENT_DISPATCHERS);

        /**
         * @var string $name
         * @var string $serviceId
         */
        foreach ($entityManagers as $name => $serviceId) {
            $service = $container->getDefinition($serviceId);
            $realServiceId = $serviceId . '.real';

            $container
                ->setDefinition($realServiceId, $service);

            $container
                ->register($serviceId, DomainEventAwareEntityManager::class)
                ->setArguments([
                    '$wrapped' => new Reference($realServiceId),
                    '$eventDispatchers' => $eventDispatchers,
                ])
                ->setPublic(true)
                ->addTag('kernel.reset', ['method' => 'reset'])
                ->addTag('rekalogika.domain_event.entity_manager', ['name' => $name]);
        }
    }
}
