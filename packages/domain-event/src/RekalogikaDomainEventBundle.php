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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RekalogikaDomainEventBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EntityManagerDecoratorPass());
    }

    public function boot(): void
    {
        $installer = $this->container?->get(ImmediateDomainEventDispatcherInstaller::class);

        if ($installer instanceof ImmediateDomainEventDispatcherInstaller) {
            $installer->install();
        }
    }
}
