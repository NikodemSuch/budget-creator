<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->scalarNode('invitation_expiration_time')
                    ->defaultValue('7 days')
                ->end()
                ->scalarNode('notification_visibility_time')
                    ->defaultValue('30 days')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
