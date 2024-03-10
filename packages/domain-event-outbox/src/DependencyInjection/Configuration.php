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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rekalogika_domain_event_outbox');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('messenger_transport')
                    ->info('Messenger transport ID used to send the outbox messages.')
                    ->defaultValue('rekalogika.domain_event.transport')
                ->end()

                ->scalarNode('outbox_table')
                    ->info('Table name used to store the outbox messages.')
                    ->defaultValue('rekalogika_event_outbox')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
