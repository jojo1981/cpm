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
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Package\Version\VersionParser;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface;
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\File\JsonFile
 */
abstract class JsonFile
{

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * The filename of this json file
     *
     * @var string
     */
    protected $fileName;

    /**
     * The base path, this is the path where the main packages.json file
     * is located.
     *
     * @var string
     */
    protected $basePath;

    /**
     * @var array
     */
    protected $data;

    /**
     * The sha method which has been used to calculate the hash for this
     * file in the providers or includes list. this method can be 'sha1'
     * or 'sha256', maybe others to come.
     *
     * @var string
     */
    protected $shaMethod = 'sha256';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var null|DownloaderInterface
     */
    protected $downloader;

    /**
     * @var string
     */
    protected $providersUrl = '';

    /**
     * Inject the config object to use to read the config
     * setting from. With this ParseConfig object you can
     * determine the parse behavior
     *
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Set the output interface to use for writing messages to.
     * Can be used if this class is used in a Console Command.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @void
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Inject the downloader to use in case the file can't be found
     * locally, if no downloader is injected the a file not found
     * exception will be thrown.
     *
     * @param DownloaderInterface $downloader
     */
    public function setDownloader(DownloaderInterface $downloader = null)
    {
        $this->downloader = $downloader;
    }

    /**
     * This method start parsing this JSON file it can invoke
     * several other JSON file to be parsed. So a chain reaction
     * can be started.
     *
     * @param bool $writeToDisk
     */
    abstract public function parse($writeToDisk = true);

    /**
     * Get hash string for this file using the sha method
     * which has been supplied through the constructor
     *
     * @return string
     */
    public function getHash()
    {
        $content = json_encode($this->data);
        switch ($this->shaMethod) {
            case 'sha1':
                $hash = sha1($content);
                break;
            case 'sha256':
                $hash = hash('sha256', $content);
                break;
        }

        return $hash;
    }

    /**
     * Get the current filename, the filename can be changed
     * when this was needed because of the provider required this
     * and because the hash for this file has changed.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Parse packages, if allowed from ParseConfig object.
     */
    protected function parsePackages()
    {
        $prefixDownloadUri = $this->config->getDownloadUrlPrefix();
        if (isset($this->data['packages'])) {
            foreach ($this->data['packages'] as $packageName => $packageData) {
                foreach ($packageData as $version => $data) {
                    if ($this->config->getParse() && isset($this->data['packages'])) {

                        $stability = VersionParser::parseStability($version);
                        $doParse = $stability !== 'dev';

                        if ($doParse) {
                            $data['dist'] = array(
                                'type'      => 'zip',
                                'reference' => $version,
                                'shasum'    => '',
                                'url'       => $prefixDownloadUri . $packageName . '/' . $version . '.zip'
                            );
                        }
                    }

                    if (isset($data['uid']) === false) {
                        $data['uid'] = $this->config->getUid();
                    }

                    $this->data['packages'][$packageName][$version] = $data;
                }
            }
        }
    }

    protected function setData($fileName, $basePath = '')
    {
        $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        if ($basePath == '') {
            $basePath = dirname($fileName);
        }
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);

        if (substr($basePath, -1) != DIRECTORY_SEPARATOR) {
            $basePath .= DIRECTORY_SEPARATOR;
        }

        if (file_exists($fileName) === false) {
            if ($this->downloader instanceof DownloaderInterface) {
                $urlPath = str_replace(
                    array($basePath, DIRECTORY_SEPARATOR),
                    array('', '/'),
                    $fileName
                );
                $content = $this->downloader->download($urlPath);
                $fs = new Filesystem();
                $fs->mkdir(dirname($fileName));
                file_put_contents($fileName, $content);
            } else {
                throw new \RuntimeException('File does not exists: ' .  $fileName);
            }
        }

        $this->fileName = $fileName;
        $this->basePath = $basePath;
        $this->data = json_decode(file_get_contents($this->fileName), true);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}
