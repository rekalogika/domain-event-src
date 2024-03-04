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

namespace Rekalogika\DomainEvent\Tests\Framework;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Rekalogika\DomainEvent\RekalogikaDomainEventBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Kernel as HttpKernelKernel;

class Kernel extends HttpKernelKernel
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(
        string $environment = 'test',
        bool $debug = true,
        private array $config = []
    ) {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new RekalogikaDomainEventBundle(),
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/Resources/config')
        );

        $loader->load('services_test.php');
    }

    public function getProjectDir(): string
    {
        return dirname(dirname(__DIR__));
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/Resources/config/';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getConfigDir() . '/packages/*' . '.yaml', 'glob');

        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('rekalogika_domain_event', $this->config);
        });
    }
}
