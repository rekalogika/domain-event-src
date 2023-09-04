<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Rekalogika\DomainEvent\RekalogikaDomainEventBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as HttpKernelKernel;

class Kernel extends HttpKernelKernel
{
    public function registerBundles(): iterable
    {
        return [
            new RekalogikaDomainEventBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}
