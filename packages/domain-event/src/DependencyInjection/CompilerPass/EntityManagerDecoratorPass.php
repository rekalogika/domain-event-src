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

use Rekalogika\DomainEvent\Contracts\DomainEventManagerInterface;
use Rekalogika\DomainEvent\Doctrine\DomainEventAwareEntityManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final readonly class EntityManagerDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        assert(is_array($entityManagers));

        $domainEventManager = $container->getDefinition(DomainEventManagerInterface::class);

        /**
         * @var string $name
         * @var string $id
         */
        foreach ($entityManagers as $name => $id) {
            $service = $container->getDefinition($id);
            $decoratedServiceId = $id . '.domain_event_aware';

            $container->register($decoratedServiceId, DomainEventAwareEntityManager::class)
                ->setDecoratedService($id)
                ->setArguments([
                    $service,
                    $domainEventManager,
                ])
                ->addTag('kernel.reset', ['method' => 'reset']);
        }
    }
}
