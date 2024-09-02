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

namespace Rekalogika\DomainEvent\Outbox\DependencyInjection;

use Rekalogika\Contracts\DomainEvent\Attribute\AsPublishedDomainEventListener;
use Rekalogika\DomainEvent\Outbox\MessagePreparerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class RekalogikaDomainEventOutboxExtension extends Extension
{
    /**
     * @param array<array-key,mixed> $configs
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $debug = (bool) $container->getParameter('kernel.debug');

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );
        $loader->load('services.php');

        if ($debug) {
            $loader->load('debug.php');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $outboxTable = $config['outbox_table'] ?? null;
        \assert(\is_string($outboxTable), 'The "outbox_table" option must be a string.');
        $container->setParameter('rekalogika.domain_event.outbox.outbox_table', $outboxTable);

        $container
            ->registerForAutoconfiguration(MessagePreparerInterface::class)
            ->addTag('rekalogika.domain_event.outbox.message_preparer');

        $container->registerAttributeForAutoconfiguration(
            AsPublishedDomainEventListener::class,
            static function (
                ChildDefinition $definition,
                AsPublishedDomainEventListener $attribute,
                \Reflector $reflector,
            ): void {
                if (
                    !$reflector instanceof \ReflectionClass
                    && !$reflector instanceof \ReflectionMethod
                ) {
                    return;
                }

                $tagAttributes = get_object_vars($attribute);
                $tagAttributes['bus'] = 'rekalogika.domain_event.bus';
                if ($reflector instanceof \ReflectionMethod) {
                    if (isset($tagAttributes['method'])) {
                        throw new \LogicException(sprintf('AsPreFlushDomainEventListener attribute cannot declare a method on "%s::%s()".', $reflector->class, $reflector->name));
                    }

                    $tagAttributes['method'] = $reflector->getName();
                }

                $definition->addTag(
                    'messenger.message_handler',
                    $tagAttributes,
                );
            },
        );
    }
}
