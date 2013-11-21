<?php

namespace M6Web\GithubEnterpriseArchiveBundle\DependencyInjection;

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
        $rootNode    = $treeBuilder->root('m6_web_github_enterprise_archive');

        $rootNode->children()
            ->scalarNode('base_url')->isRequired()->end()
            ->scalarNode('data_dir')->defaultValue('%kernel.root_dir%/data/archive')->end();

        return $treeBuilder;
    }
}
 