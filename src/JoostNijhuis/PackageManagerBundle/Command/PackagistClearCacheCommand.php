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
 * Packagist Clear Cache Command class
 * With this command the whole Packagist cache can be cleared
 */
class PackagistClearCacheCommand extends ContainerAwareCommand
{

    /**
     * This method will be used by the Symfony2 Console Component
     * to setup the requirements for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:clear_packagist_cache')
            ->setAliases(array('joost_nijhuis_package_manager:clear_packagist_cache'))
            ->setDescription('Clear the local packagist cache')
            ->setHelp(<<<EOT
The <info>clear_packagist_cache</info> command clears the local packagist cache.
EOT
        );
    }

    /**
     * This execute method will be triggered by the Symfony2 Console
     * Component. A InputInterface and OutputInterface will be injected
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input The input instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheHandler = $this->getContainer()
            ->get('joost_nijhuis_package_manager_cache_handler')
        ;

        $cacheHandler->setInputInterface($input);
        $cacheHandler->setOutputInterface($output);

        $cacheHandler->cleanCache();
    }

}
