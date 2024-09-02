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
use Rekalogika\DomainEvent\Outbox\RekalogikaDomainEventOutboxBundle;
use Rekalogika\DomainEvent\RekalogikaDomainEventBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
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
        private readonly array $config = []
    ) {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new WebProfilerBundle(),
            new TwigBundle(),
            new DebugBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new RekalogikaDomainEventBundle(),
            new RekalogikaDomainEventOutboxBundle(),
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
        return \dirname(__DIR__, 2);
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/Resources/config/';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getConfigDir() . '/packages/*' . '.yaml', 'glob');

        $loader->load(function (ContainerBuilder $container): void {
            $container->loadFromExtension('rekalogika_domain_event', $this->config);
        });
    }
}
