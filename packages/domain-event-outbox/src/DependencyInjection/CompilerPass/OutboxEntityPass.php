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

use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class OutboxEntityPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        \assert(\is_array($entityManagers));

        // doctrine-bundle 2.x defaults `reportFieldsWhereDeclared` to false,
        // which crashes on ORM 3.x. The argument was removed in bundle 3.x.
        $bundleVersion = InstalledVersions::getVersion('doctrine/doctrine-bundle');
        $passReportFieldsWhereDeclared = null !== $bundleVersion
            && version_compare($bundleVersion, '3.0.0', '<');

        /**
         * @var string $name
         */
        foreach (array_keys($entityManagers) as $name) {
            $parameterKey = \sprintf('rekalogika.domain_event.doctrine.orm.%s_entity_manager', $name);
            $container->setParameter($parameterKey, $name);

            $path = realpath(__DIR__ . '/../../Entity');
            if (false === $path) {
                throw new \RuntimeException('Entity path not found');
            }

            if ($passReportFieldsWhereDeclared) {
                /** @psalm-suppress InvalidNamedArgument doctrine-bundle 2.x only */
                $pass = DoctrineOrmMappingsPass::createAttributeMappingDriver(
                    namespaces: ['Rekalogika\DomainEvent\Outbox\Entity'],
                    directories: [$path],
                    managerParameters: [$parameterKey],
                    reportFieldsWhereDeclared: true,
                );
            } else {
                $pass = DoctrineOrmMappingsPass::createAttributeMappingDriver(
                    namespaces: ['Rekalogika\DomainEvent\Outbox\Entity'],
                    directories: [$path],
                    managerParameters: [$parameterKey],
                );
            }

            $pass->process($container);

            $container->getParameterBag()->remove($parameterKey);
        }
    }
}
