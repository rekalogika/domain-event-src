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

namespace Rekalogika\DomainEvent;

use Rekalogika\DomainEvent\DependencyInjection\CompilerPass\EntityManagerDecoratorPass;
use Rekalogika\DomainEvent\DependencyInjection\CompilerPass\ProfilerWorkaroundPass;
use Rekalogika\DomainEvent\DependencyInjection\Constants;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RekalogikaDomainEventBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EntityManagerDecoratorPass());
        $container->addCompilerPass(new ProfilerWorkaroundPass());
    }

    public function boot(): void
    {
        $installer = $this->container?->get(Constants::IMMEDIATE_DISPATCHER_INSTALLER);

        if ($installer instanceof ImmediateDomainEventDispatcherInstaller) {
            /** @var ImmediateDomainEventDispatcherInstaller $installer */
            $installer->install();
        }
    }

    public function shutdown(): void
    {
        $installer = $this->container?->get(Constants::IMMEDIATE_DISPATCHER_INSTALLER);

        if ($installer instanceof ImmediateDomainEventDispatcherInstaller) {
            /** @var ImmediateDomainEventDispatcherInstaller $installer */
            $installer->uninstall();
        }
    }
}
