<?php

namespace M6Web\StatHatBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('m6_web_stat_hat');

        $rootNode
            ->canBeEnabled()
            ->children()
                ->scalarNode('ez_key')->isRequired()->end()
                ->arrayNode('counts')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('event')->isRequired()->end()
                        ->scalarNode('stat_key')->isRequired()->end()
                        ->scalarNode('count')->defaultValue(1)->end()
                        ->scalarNode('timestamp')->end();

        return $treeBuilder;
    }
}
 