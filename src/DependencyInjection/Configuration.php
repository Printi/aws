<?php

namespace Printi\AwsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('printi_aws');
        $root
            ->children()
                ->arrayNode('global')
                    ->useAttributeAsKey('name')
                    ->children()
                        ->scalarNode('version')
                        ->scalarNode('region')
                            ->defaultValue('us-east-1')
                        ->arrayNode('credentials')
                            ->useAttributeAsKey('name')
                            ->children()
                                ->scalarNode('key')
                                ->scalarNode('secret')
                ->arrayNode('s3')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('sqs')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('sns')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
            ->end();

        return $builder;
    }
}
