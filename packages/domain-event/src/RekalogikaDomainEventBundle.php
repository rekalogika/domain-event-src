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

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RekalogikaDomainEventBundle extends Bundle
{
    public function boot(): void
    {
        $installer = $this->container?->get(ImmediateDomainEventDispatcherInstaller::class);

        if ($installer instanceof ImmediateDomainEventDispatcherInstaller) {
            $installer->install();
        }
    }
}
