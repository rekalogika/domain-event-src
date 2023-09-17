<?php

/*
 * This file is part of rekalogika/domain-event package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\DependencyInjection;

use Rekalogika\Contracts\DomainEvent\Attribute\AsImmediateDomainEventListener;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPostFlushDomainEventListener;
use Rekalogika\Contracts\DomainEvent\Attribute\AsPreFlushDomainEventListener;
use Rekalogika\DomainEvent\Constants;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class RekalogikaDomainEventExtension extends Extension
{
    /**
     * @param array<array-key,array<array-key,mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $env = $container->getParameter('kernel.environment');

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.php');

        if ('test' === $env) {
            $loader->load('services_test.php');
        }

        $container->registerAttributeForAutoconfiguration(
            AsPostFlushDomainEventListener::class,
            static function (
                ChildDefinition $definition,
                AsPostFlushDomainEventListener $attribute,
                \Reflector $reflector
            ): void {
                if (
                    !$reflector instanceof \ReflectionClass
                    && !$reflector instanceof \ReflectionMethod
                ) {
                    return;
                }

                $tagAttributes = get_object_vars($attribute);
                $tagAttributes['dispatcher'] = Constants::EVENT_DISPATCHER_POST_FLUSH;
                if ($reflector instanceof \ReflectionMethod) {
                    if (isset($tagAttributes['method'])) {
                        throw new \LogicException(sprintf('AsPostFlushDomainEventListener attribute cannot declare a method on "%s::%s()".', $reflector->class, $reflector->name));
                    }
                    $tagAttributes['method'] = $reflector->getName();
                }
                $definition->addTag(
                    'kernel.event_listener',
                    $tagAttributes
                );
            }
        );

        $container->registerAttributeForAutoconfiguration(
            AsPreFlushDomainEventListener::class,
            static function (
                ChildDefinition $definition,
                AsPreFlushDomainEventListener $attribute,
                \Reflector $reflector
            ): void {
                if (
                    !$reflector instanceof \ReflectionClass
                    && !$reflector instanceof \ReflectionMethod
                ) {
                    return;
                }

                $tagAttributes = get_object_vars($attribute);
                $tagAttributes['dispatcher'] = Constants::EVENT_DISPATCHER_PRE_FLUSH;
                if ($reflector instanceof \ReflectionMethod) {
                    if (isset($tagAttributes['method'])) {
                        throw new \LogicException(sprintf('AsPreFlushDomainEventListener attribute cannot declare a method on "%s::%s()".', $reflector->class, $reflector->name));
                    }
                    $tagAttributes['method'] = $reflector->getName();
                }
                $definition->addTag(
                    'kernel.event_listener',
                    $tagAttributes
                );
            }
        );

        $container->registerAttributeForAutoconfiguration(
            AsImmediateDomainEventListener::class,
            static function (
                ChildDefinition $definition,
                AsImmediateDomainEventListener $attribute,
                \Reflector $reflector
            ): void {
                if (
                    !$reflector instanceof \ReflectionClass
                    && !$reflector instanceof \ReflectionMethod
                ) {
                    return;
                }

                $tagAttributes = get_object_vars($attribute);
                $tagAttributes['dispatcher'] = Constants::EVENT_DISPATCHER_IMMEDIATE;
                if ($reflector instanceof \ReflectionMethod) {
                    if (isset($tagAttributes['method'])) {
                        throw new \LogicException(sprintf('AsImmediateDomainEventListener attribute cannot declare a method on "%s::%s()".', $reflector->class, $reflector->name));
                    }
                    $tagAttributes['method'] = $reflector->getName();
                }
                $definition->addTag(
                    'kernel.event_listener',
                    $tagAttributes
                );
            }
        );
    }
}
