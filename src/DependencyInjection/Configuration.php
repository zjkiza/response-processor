<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zjkiza_response_processor');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('priority')
                            ->defaultValue(45)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
