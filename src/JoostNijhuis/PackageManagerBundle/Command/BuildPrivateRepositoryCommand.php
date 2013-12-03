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

use JoostNijhuis\PackageManagerBundle\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JoostNijhuis\PackageManagerBundle\Builder\RepositoryBuilder;

/**
 * JoostNijhuis\PackageManagerBundle\Command\BuildPrivateRepositoryCommand
 *
 * Update Repository Command class
 * With this command the private packages part of the repository,
 * the packages who are not registered on packagist can be updated.
 * If there are any new versions in SVN or on GitHup etc... they will be
 * registered into the private packages part of the repository.
 */
class BuildPrivateRepositoryCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:build:private_repository')
            ->setAliases(array('joost_nijhuis_package_manager:build:private_repository'))
            ->setDescription('Build private packages repository')
            ->setDefinition(array())
            ->setHelp(<<<EOT
The <info>%command.name%</info> command reads the configured json file
and outputs a composer repository in the configured output-dir.
EOT
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getProcessName()
    {
        return 'build';
    }

    /**
     * {@inheritDoc}
     */
    protected function mustWait()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTimeOut()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function runProcess(
        InputInterface $input,
        OutputInterface $output
    ) {
        /** @var RepositoryBuilder $builder */
        $builder = $this->getContainer()->get(
            'joost_nijhuis_package_manager.repository_builder'
        );

        $builder->buildPrivateRepository(
            $output,
            $input,
            $this->getHelperSet()
        );
    }
}
