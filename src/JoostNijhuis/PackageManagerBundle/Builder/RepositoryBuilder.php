<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Builder;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Filesystem\Filesystem;
use JoostNijhuis\PackageManagerBundle\Builder\File\PackagesJson;
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\Downloader;
use JoostNijhuis\PackageManagerBundle\Builder\PrivateRepositoryBuilder;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\RepositoryBuilder
 */
class RepositoryBuilder
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var PrivateRepositoryBuilder
     */
    protected $builder;

    /**
     * Constructor
     *
     * @param Config $config
     * @param PrivateRepositoryBuilder $builder
     */
    public function __construct(
        Config $config,
        PrivateRepositoryBuilder $builder
    ) {
        $this->config  = $config;
        $this->builder = $builder;

        $config->getBaseUrl();
        $this->downloader = new Downloader($config->getBaseUrl());
    }

    /**
     * Start building new index
     *
     * @param OutputInterface $output
     */
    public function buildRepository(OutputInterface $output)
    {
        $fileName = implode(DIRECTORY_SEPARATOR, array(
            $this->config->getTmpPath(),
            'composer',
            'packages.json'
        ));

        /* Build, attach private packages and parse whole index */
        $packagesJson = new PackagesJson($fileName, $this->downloader);
        $packagesJson->setConfig($this->config);
        $packagesJson->setOutputInterface($output);
        $packagesJson->parse();

        $indexPath = $this->config->getIndexPath();
        $indexPathTmp = $this->config->getIndexPath() . '_' . date('YmdHis');

        $fs = new Filesystem();
        $fs->mirror(dirname($fileName), $indexPathTmp);
        $fs->remove(dirname($fileName));
        $fs->remove($indexPath);
        $fs->symlink($indexPathTmp, $indexPath, true);
    }

    /**
     * Start building new index
     *
     * @param OutputInterface $output
     * @param InputInterface $input
     * @param HelperSet $helperSet
     */
    public function buildPrivateRepository(
        OutputInterface $output,
        InputInterface $input,
        HelperSet $helperSet
    ) {
//        $this->builder->setOutputInterface($output);
//        $this->builder->setInputInterface($input);
//        $this->builder->setHelperSet($helperSet);
//        $this->builder->buildRepository();
//
        $fileName = implode(DIRECTORY_SEPARATOR, array(
            $this->config->getIndexPath(),
            'packages.json'
        ));

        /* Build, attach private packages and parse whole index */
        $packagesJson = new PackagesJson($fileName, $this->downloader);
        $packagesJson->setConfig($this->config);
        $packagesJson->setOutputInterface($output);
        $packagesJson->attachPrivatePackages();
    }

}
