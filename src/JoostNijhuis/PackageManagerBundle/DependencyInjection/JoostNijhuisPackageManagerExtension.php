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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * The Extention class which is responsible for setting up
 * the bundle configurations and register all services inside this
 * bundle into the Dependency Injection Container
 */
class JoostNijhuisPackageManagerExtension extends Extension
{

    /**
     * Will be triggered when the Symfony2 kernel initializes this bundle
     *
     * @param array $configs the array with all configuration read from the config.yml file
     *                       for this bunlde
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {       
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('joost_nijhuis_package_manager.app_name', $config['app_name']);
        $container->setParameter('joost_nijhuis_package_manager.company_name', $config['company_name']);
        if (isset($config['company_url'])) {
            $container->setParameter('joost_nijhuis_package_manager.company_url', $config['company_url']);
        }
        $container->setParameter('joost_nijhuis_package_manager.parse_only_stable', $config['parse_only_stable']);
        $container->setParameter('joost_nijhuis_package_manager.enable_cache', $config['enable_cache']);
        $container->setParameter('joost_nijhuis_package_manager.packagist_url', $config['packagist_url']);
        $container->setParameter('joost_nijhuis_package_manager.private_packages_config_file', realpath($config['private_packages_config_file']));
        $container->setParameter('joost_nijhuis_package_manager.private_packages_output_file', realpath($config['private_packages_output_file']));
        $container->setParameter('joost_nijhuis_package_manager.private_packages_data_dir', realpath($config['private_packages_data_dir']));
        $container->setParameter('joost_nijhuis_package_manager.packagist_cache_dir', realpath($config['packagist_cache_dir']));
        $container->setParameter('joost_nijhuis_package_manager.tmp_dir', realpath($config['tmp_dir']));
        $container->setParameter('joost_nijhuis_package_manager.params', $config);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

}
