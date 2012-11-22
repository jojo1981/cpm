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

/**
 * Update Repostitory Command class
 * With this command the private packages part of the repository,
 * the packages who are not registered on packagist can be updated.
 * If there are any new versions in SVN or on GitHup etc... they will be
 * registered into the private packages part of the repository.
 */
class UpdateRepositoryCommand extends ContainerAwareCommand
{

    /**
     * This method will be used by the Symfony2 Console Component
     * to setup the requirements for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:repository_update')
             ->setAliases(array('joost_nijhuis_package_manager:repository_update'))
             ->setDescription('Update this composer repository')
             ->setDefinition(array())
             ->setHelp(<<<EOT
The <info>repository_update</info> command reads the configured json file
and outputs a composer repository in the configured output-dir.
EOT
        );
    }

    /**
     * This method will be triggered by the Symfony2 Console Component.
     * A InputInterface and OutputInterface will be injected
     *
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \JoostNijhuis\PackageManagerBundle\ComposerRepository\BuildHandler $builder */
        $builder = $this->getContainer()->get('joost_nijhuis_package_manager_repository_build_handler');

        $builder->setOutputInterface($output);
        $builder->setInputInterface($input);
        $builder->setHelperSet($this->getHelperSet());

        $builder->buildRepository();
    }

}
