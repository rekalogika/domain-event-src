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

namespace Rekalogika\DomainEvent\Outbox\DependencyInjection\CompilerPass;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class OutboxEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        assert(is_array($entityManagers));

        /**
         * @var string $name
         * @var string $id
         */
        foreach ($entityManagers as $name => $id) {
            $parameterKey = sprintf('rekalogika.domain_event.doctrine.orm.%s_entity_manager', $name);
            $container->setParameter($parameterKey, $name);

            $path = realpath(__DIR__ . '/../../Entity');
            if (false === $path) {
                throw new \RuntimeException('Entity path not found');
            }

            $pass = DoctrineOrmMappingsPass::createAttributeMappingDriver(
                namespaces: ['Rekalogika\DomainEvent\Outbox\Entity'],
                directories: [$path],
                managerParameters: [$parameterKey],
                reportFieldsWhereDeclared: true,
            );

            $pass->process($container);

            $container->getParameterBag()->remove($parameterKey);
        }
    }
}
