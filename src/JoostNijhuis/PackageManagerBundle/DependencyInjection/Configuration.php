<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The Configuration class which will be used to setup
 * all configuration options for this bundle
 * These are the options which can be set and/or overriden in the
 * config.yml file
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Returns a tree with all options with there configurations
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('joost_nijhuis_package_manager');

        $rootNode
            ->children()
                ->scalarNode('app_name')
                    ->defaultValue('[Company name]')
                ->end()
                ->scalarNode('company_name')
                    ->defaultValue('[Company name]')
                ->end()
                ->scalarNode('company_url')
                ->end()
                ->booleanNode('parse_only_stable')
                    ->defaultTrue()
                ->end()
                    ->booleanNode('enable_cache')
                    ->defaultFalse()
                ->end()
                ->scalarNode('packagist_url')
                    ->defaultValue('http://packagist.org')
                ->end()
                ->scalarNode('private_packages_config_file')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('private_packages_output_file')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('private_packages_data_dir')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('packagist_cache_dir')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('tmp_dir')
                    ->defaultValue(sys_get_temp_dir())
                ->end()
            ->end();

        return $treeBuilder;
    }

}
