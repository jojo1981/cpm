<?php

namespace JoostNijhuis\PackageManagerBundle\Builder;

use Symfony\Component\Filesystem\Filesystem;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface;


/**
 * JoostNijhuis\PackageManagerBundle\Builder\PackagesJson
 *
 * This object has a direct link with the packages.json file. The main file
 * of a Composer Repository.
 */
class PackagesJson extends JsonFile
{

    protected $start;

    /**
     * Constructor
     *
     * @param string $fileName
     * @param Downloader\DownloaderInterface $downloader [optional]
     */
    public function __construct($fileName, DownloaderInterface $downloader = null)
    {
        $this->setDownloader($downloader);
        $this->setData($fileName);

        $this->providersUrl = $this->data['providers-url'];
        $this->config = new ParseConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function parse()
    {
        $this->start = microtime(true);
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

        $this->parsePackages();
        $this->getProviderIncludes();
        $this->getProvidersIncludes();
        $this->getIncludes();
        $this->writeFileToDisc();
        $timeTaken = microtime(true) - $this->start;
        $timeTakenMinutes = $timeTaken / 60;
        $this->output->writeln('Take about: ' . $timeTakenMinutes . ' minutes.');
    }

    protected function getProvidersIncludes()
    {
        foreach ($this->data['providers-includes'] as $fileName => $data) {

            $shaMethod = current(array_keys($data));
            $hash = array_shift($data);

            $providerContainer = new ProviderContainer(
                $this->basePath . $fileName,
                $this->basePath,
                $shaMethod,
                '',
                '',
                $this->downloader
            );
            $providerContainer->setConfig($this->config);
            $providerContainer->setOutputInterface($this->output);
            $providerContainer->setInputInterface($this->input);
            $providerContainer->parse();

            if ($providerContainer->getHash() != $hash) {
                $this->data['providers-includes'][$fileName] = array(
                    $shaMethod => $providerContainer->getHash()
                );
            }
        }
    }

    protected function getProviderIncludes()
    {
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
            $providerContainer->setInputInterface($this->input);
            $providerContainer->parse();

            if ($providerContainer->getHash() != $hash) {
                $this->data['provider-includes'][$oldFileName] = array(
                    $shaMethod => $providerContainer->getHash()
                );
            }
        }
    }

    protected function getIncludes()
    {
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
            $packageContainer->setInputInterface($this->input);
            $packageContainer->parse();

            if ($packageContainer->getHash() != $hash) {
                $this->data['includes'][$fileName] = array(
                    $shaMethod => $packageContainer->getHash()
                );
            }
        }
    }

    protected function writeFileToDisc()
    {
        $fs = new Filesystem();
        $fs->mkdir(dirname($this->fileName));
        file_put_contents($this->fileName, json_encode($this->data));
        $this->output->writeln('Main file saved to disc: ' . $this->fileName);
    }

}
