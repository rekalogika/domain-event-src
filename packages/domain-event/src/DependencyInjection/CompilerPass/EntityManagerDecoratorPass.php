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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Rekalogika\DomainEvent\Doctrine\LazyDomainEventAwareEntityManager;
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
 * The real entity managers are kept public under `<id>.real`, and a separate
 * Doctrine registry pointing to them is registered. Our manager registry uses
 * it to reset the real entity manager in place when an entity manager is
 * reset, so that a closed entity manager gets reopened. Otherwise, resetting
 * the decorator recreates it around the same closed instance.
 *
 * @internal
 */
final class EntityManagerDecoratorPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if (\PHP_VERSION_ID < 80400) {
            $decoratorClass = LazyDomainEventAwareEntityManager::class;
        } else {
            $decoratorClass = DomainEventAwareEntityManager::class;
        }

        $entityManagers = $container->getParameter('doctrine.entity_managers');
        \assert(\is_array($entityManagers));

        $eventDispatchers = $container->getDefinition(Constants::EVENT_DISPATCHERS);

        $realEntityManagers = [];

        /**
         * @var string $name
         * @var string $serviceId
         */
        foreach ($entityManagers as $name => $serviceId) {
            $service = $container->getDefinition($serviceId);
            $realServiceId = $serviceId . '.real';
            $realEntityManagers[$name] = $realServiceId;

            // must be public so that the registry can reset it
            $service->setPublic(true);

            $container
                ->setDefinition($realServiceId, $service);

            $decoratorDefinition = $container
                ->register($serviceId, $decoratorClass)
                ->setArguments([
                    '$wrapped' => new Reference($realServiceId),
                    '$eventDispatchers' => $eventDispatchers,
                ])
                ->setPublic(true)
                ->addTag('kernel.reset', ['method' => 'reset'])
                ->addTag('rekalogika.domain_event.entity_manager', ['name' => $name]);

            if (\PHP_VERSION_ID >= 80400) {
                $decoratorDefinition->setLazy(true);
            }
        }

        $container
            ->register(Constants::REAL_ENTITY_MANAGER_REGISTRY, Registry::class)
            ->setArguments([
                new Reference('service_container'),
                '%doctrine.connections%',
                $realEntityManagers,
                '%doctrine.default_connection%',
                '%doctrine.default_entity_manager%',
            ]);

        $container
            ->getDefinition(Constants::MANAGER_REGISTRY)
            ->setArgument(
                '$realEntityManagerRegistry',
                new Reference(Constants::REAL_ENTITY_MANAGER_REGISTRY),
            );
    }
}
