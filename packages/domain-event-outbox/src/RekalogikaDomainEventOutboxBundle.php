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

namespace Rekalogika\DomainEvent\Outbox;

use Rekalogika\DomainEvent\Outbox\DependencyInjection\CompilerPass\OutboxEntityPass;
use Rekalogika\DomainEvent\Outbox\DependencyInjection\CompilerPass\RemoveUnusedPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RekalogikaDomainEventOutboxBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OutboxEntityPass());
        $container->addCompilerPass(new RemoveUnusedPass());
    }
}
