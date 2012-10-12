<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Packagist Renew Cache Command class
 * With this command the whole Packagist cache can be renewed
 */
class PackagistRenewCacheCommand extends ContainerAwareCommand
{

    /**
     * This method will be used by the Symfony2 Console Component
     * to setup the requirements for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:renew_packagist_cache')
            ->setAliases(array('joost_nijhuis_package_manager:renew_packagist_cache'))
            ->setDescription('Renews the local packagist cache')
            //->setDefinition(array())
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If set, the whole cache will be renewed')
            ->setHelp(<<<EOT
The <info>renew_packagist_cache</info> command renews the local packagist cache.
EOT
        );
    }

    /**
     * The execute method will be triggered by the Symfony2 Console
     * Component. A InputInterface and OutputInterface will be injected
     *
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheHandler = $this->getContainer()
            ->get('joost_nijhuis_package_manager_cache_handler')
        ;
        $cacheHandler->setInputInterface($input);
        $cacheHandler->setOutputInterface($output);
        $cacheHandler->setForceRenewWholeCache($input->getOption('force'));
        $cacheHandler->renewCache();
    }

}
