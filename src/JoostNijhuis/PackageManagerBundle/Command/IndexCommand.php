<?php

namespace JoostNijhuis\PackageManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JoostNijhuis\PackageManagerBundle\Builder\Indexer;
use JoostNijhuis\PackageManagerBundle\Builder\PackagesJson;
use JoostNijhuis\PackageManagerBundle\Builder\ParseConfig;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\Downloader;

class IndexCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('joost_nijhuis_package_manager:packagist:index')
            ->setAliases(array('joost_nijhuis_package_manager:packagist:index'))
            ->setDescription('Index packagist composer repository')
            ->setDefinition(array())
            ->setHelp(<<<EOT
The <info>joost_nijhuis_package_manager:packagist:index</info> create a loccaly
index of the whole packagist repository.
EOT
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Bridge\Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $cacheDir = $rootDir . '/cache/composer';
        $privatePackagesFile = realpath(
            $this->getContainer()->getParameter(
                'private_packages_output_file'
            )
        );
        $baseUrl = 'http://packagist.org';

//        $indexer = new Indexer($cacheDir, $baseUrl);
//        $indexer->setOutputInterface($output);
//        $indexer->setInputInterface($input);
//        $indexer->setHelperSet($this->getHelperSet());
//
//        $indexer->index();

        $fileName = $cacheDir . DIRECTORY_SEPARATOR . 'packages.json';

        $config = new ParseConfig();
        $config->setPrivatePackagesFile($privatePackagesFile);
        $config->setAttachPrivatePackages(true);

        $downloader = new Downloader($baseUrl);

        $packagesJson = new PackagesJson($fileName, $downloader);
        $packagesJson->setConfig($config);
        $packagesJson->setOutputInterface($output);
        $packagesJson->setInputInterface($input);
        $packagesJson->parse();
    }

}
