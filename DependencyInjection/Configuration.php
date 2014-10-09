<?php
/**
 * AutoTablesBundle
 * Copyright (c) 2014, 20steps Digital Full Service Boutique, All rights reserved.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.
 */

namespace twentysteps\Bundle\AutoTablesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('twentysteps_auto_tables');

        $rootNode
            ->children()
                ->scalarNode('default_datatables_options')->end()
                ->enumNode('frontend_framework')->values(array('bootstrap3', 'jquery-ui'))->end()
                ->arrayNode('tables')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->isRequired()->end()
                            ->scalarNode('service')->end()
                            ->scalarNode('repository')->end()
                            ->scalarNode('trans_scope')->end()
                            ->scalarNode('datatables_options')->end()
                            ->scalarNode('views')->end()
                            ->arrayNode('columns')
                                ->useAttributeAsKey('selector')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('selector')->end()
                                        ->scalarNode('name')->end()
                                        ->booleanNode('readOnly')->end()
                                        ->scalarNode('type')->end()
                                        ->scalarNode('viewType')->end()
                                        ->integerNode('order')->end()
                                        ->booleanNode('ignore')->end()
                                        ->booleanNode('visible')->end()
                                        ->arrayNode('initializer')
                                            ->children()
                                                ->scalarNode('repository')->end()
                                                ->scalarNode('id')->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('values')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('label')->end()
                                                    ->scalarNode('value')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
