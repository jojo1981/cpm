<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Builder\File;

use Symfony\Component\Filesystem\Filesystem;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface;


/**
 * JoostNijhuis\PackageManagerBundle\Builder\File\PackagesJson
 *
 * This object has a direct link with the packages.json file. The main file
 * of a Composer Repository.
 */
class PackagesJson extends JsonFile
{
    /**
     * @var int
     */
    protected $start;

    /**
     * Constructor
     *
     * @param string $fileName
     * @param DownloaderInterface $downloader [optional]
     */
    public function __construct($fileName, DownloaderInterface $downloader = null)
    {
        $this->setDownloader($downloader);
        $this->setData($fileName);

        $this->providersUrl = $this->data['providers-url'];
    }

    /**
     * Attach private packages and only rebuild the files
     * which will be affected by this (recalculating the hashes).
     */
    public function attachPrivatePackages()
    {
        $this->parseMainData();

        if (isset($this->data['provider-includes'])) {
            $providers = $this->data['provider-includes'];
            $this->data['provider-includes'] = array(
                'p/provider-active$%hash%.json' => $providers['p/provider-active$%hash%.json']
            );
            $this->getProviderIncludes();
            $this->data['provider-includes'] = array_merge($providers, $this->data['provider-includes']);
        }

        if (isset($this->data['providers-includes'])) {
            $providers = $this->data['providers-includes'];
            $this->data['providers-includes'] = array(
                'p/providers-active.json' => $providers['p/providers-active.json']
            );
            $this->getProvidersIncludes();
            $this->data['providers-includes'] = array_merge($providers, $this->data['providers-includes']);
        }

        $this->writeFileToDisc();
    }

    /**
     * {@inheritDoc}
     */
    public function parse($writeToDisk = true)
    {
        $this->start = microtime(true);

        $this->parseMainData();
        $this->parsePackages();
        $this->getProviderIncludes();
        $this->getProvidersIncludes();
        $this->getIncludes();
        $this->writeFileToDisc();

        $timeTaken = microtime(true) - $this->start;
        $timeTakenMinutes = $timeTaken / 60;
        $this->output->writeln('Take about: ' . $timeTakenMinutes . ' minutes.');
    }

    /**
     * Parse main data
     */
    protected function parseMainData()
    {
        unset($this->data['notify_batch']);
        unset($this->data['search']);

        $notify = $this->config->getNotify();
        $notifyBatch = $this->config->getNotifyBatch();
        if (empty($notify)) {
            unset($this->data['notify']);
        } else {
            $this->data['notify'] = $notify;
        }

        if (empty($notifyBatch)) {
            unset($this->data['notify-batch']);
        } else {
            $this->data['notify-batch'] = $notifyBatch;
        }
    }

    /**
     * Parse providers includes
     */
    protected function getProvidersIncludes()
    {
        if (isset($this->data['providers-includes'])) {
            foreach ($this->data['providers-includes'] as $fileName => $data) {

                $shaMethod = current(array_keys($data));
                $hash = array_shift($data);

                $providerContainer = new ProviderContainer(
                    $this->basePath . $fileName,
                    $this->basePath,
                    $shaMethod,
                    '',
                    $fileName,
                    $this->downloader
                );
                $providerContainer->setConfig($this->config);
                $providerContainer->setOutputInterface($this->output);
                $providerContainer->parse();

                if ($providerContainer->getHash() != $hash) {
                    $this->data['providers-includes'][$fileName] = array(
                        $shaMethod => $providerContainer->getHash()
                    );
                }
            }
        }
    }

    /**
     * Parse provider includes
     */
    protected function getProviderIncludes()
    {
        if (isset($this->data['provider-includes'])) {
            foreach ($this->data['provider-includes'] as $fileName => $data) {

                $shaMethod = current(array_keys($data));
                $hash = array_shift($data);
                $oldFileName = $fileName;
                $fileName = str_replace('%hash%', $hash, $fileName);

                $providerContainer = new ProviderContainer(
                    $this->basePath . $fileName,
                    $this->basePath,
                    $shaMethod,
                    $this->providersUrl,
                    $oldFileName,
                    $this->downloader
                );
                $providerContainer->setConfig($this->config);
                $providerContainer->setOutputInterface($this->output);
                $providerContainer->parse();

                if ($providerContainer->getHash() != $hash) {
                    $this->data['provider-includes'][$oldFileName] = array(
                        $shaMethod => $providerContainer->getHash()
                    );
                }
            }
        }
    }

    /**
     * Parse includes
     */
    protected function getIncludes()
    {
        if (isset($this->data['includes'])) {
            foreach ($this->data['includes'] as $fileName => $data) {

                $shaMethod = current(array_keys($data));
                $hash = array_shift($data);

                $packageContainer = new PackageContainer(
                    $this->basePath . $fileName,
                    $this->basePath,
                    $shaMethod,
                    '',
                    '',
                    $this->downloader
                );
                $packageContainer->setConfig($this->config);
                $packageContainer->setOutputInterface($this->output);
                $packageContainer->parse();

                if ($packageContainer->getHash() != $hash) {
                    $this->data['includes'][$fileName] = array(
                        $shaMethod => $packageContainer->getHash()
                    );
                }
            }
        }
    }

    /**
     * Write file to disc
     */
    protected function writeFileToDisc()
    {
        $fs = new Filesystem();
        $fs->mkdir(dirname($this->fileName));
        file_put_contents($this->fileName, json_encode($this->data));
        $this->output->writeln('Main file saved to disc: ' . $this->fileName);
    }
}
