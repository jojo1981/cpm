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
 * JoostNijhuis\PackageManagerBundle\Command\BuildRepositoryCommand
 */
class BuildRepositoryCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:build:repository')
            ->setAliases(array('joost_nijhuis_package_manager:build:repository'))
            ->setDescription('Build the whole composer repository')
            ->setDefinition(array())
            ->setHelp(<<<EOT
The <info>%command.name%</info> create a locally
index of the whole packagist repository, add private packages and parse packages.
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
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTimeOut()
    {
        return 60*10;
    }

    /**
     * {@inheritDoc}
     */
    protected function runProcess(
        InputInterface $input,
        OutputInterface $output
    ) {
        /** @var RepositoryBuilder $repositoryBuilder */
        $repositoryBuilder = $this->getContainer()->get(
            'joost_nijhuis_package_manager.repository_builder'
        );

        $repositoryBuilder->buildRepository($output);
    }
}
