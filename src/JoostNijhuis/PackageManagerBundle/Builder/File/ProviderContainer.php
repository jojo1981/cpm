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
 * namespace JoostNijhuis\PackageManagerBundle\Builder\File\ProviderContainer
 *
 * This object contains a relationship with a JSON file
 * containing providers.
 */
class ProviderContainer extends JsonFile
{

    /**
     * @var string
     */
    protected $oldFileName = '';

    /**
     * Constructor
     *
     * @param string $fileName
     * @param string $basePath
     * @param string $shaMethod
     * @param string $providersUrl [optional]
     * @param string $oldFileName [optional]
     * @param DownloaderInterface $downloader [optional]
     */
    public function __construct(
        $fileName,
        $basePath,
        $shaMethod,
        $providersUrl = '',
        $oldFileName = '',
        DownloaderInterface $downloader = null
    ) {
        $this->setDownloader($downloader);
        $this->setData($fileName, $basePath);

        $this->shaMethod = $shaMethod;
        $this->providersUrl = $providersUrl;
        $this->oldFileName = $oldFileName;

        if (strpos($this->providersUrl, '/') === 0) {
            $this->providersUrl = substr($this->providersUrl, 1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function parse($writeToDisk = true)
    {
        $this->attachPrivatePackages();
        $this->parseProviders();
        $this->writeFileToDisc();
    }

    /**
     * Get provider files, these are actually package container
     * files, so this mean there is at least one package listed
     * in there, probably more.
     */
    protected function parseProviders()
    {
        if (isset($this->data['providers'])) {
            foreach ($this->data['providers'] as $fileName => $data) {
                $oldFileName = $fileName;
                $shaMethod = current(array_keys($data));
                $hash = array_shift($data);
                if (empty($this->providersUrl) === false) {
                    $fileName = str_replace(
                        array('%package%', '%hash%', '/'),
                        array($fileName, $hash, DIRECTORY_SEPARATOR),
                        $this->providersUrl
                    );
                }
                $packageContainer = new PackageContainer(
                    $this->basePath . $fileName,
                    $this->basePath,
                    $shaMethod,
                    $this->providersUrl,
                    $oldFileName,
                    $this->downloader
                );
                $packageContainer->setConfig($this->config);
                $packageContainer->setOutputInterface($this->output);
                $packageContainer->parse();

                if ($packageContainer->getHash() != $hash) {
                    $this->data['providers'][$oldFileName] = array(
                        $shaMethod => $packageContainer->getHash()
                    );
                }
            }
        }
    }

    /**
     * Will only be triggered if parsing was needed.
     * The content will be encoded to JSON and save to the
     * current filename, if needed the file will be renamed
     */
    protected function writeFileToDisc()
    {
        if (empty($this->oldFileName) === false) {
            $oldFileName = $this->fileName;
            $hash = $this->getHash();
            $this->fileName = $this->basePath . str_replace(
                '%hash%',
                $hash,
                $this->oldFileName
            );

            $fs = new Filesystem();
            $fs->remove($oldFileName);
            $this->output->writeln('Provider file removed from disc: ' . $oldFileName);
            $fs->mkdir(dirname($this->fileName));
        }
        file_put_contents($this->fileName, json_encode($this->data));
        $this->output->writeln('Provider file saved to disc: ' . $this->fileName);
    }

    /**
     * Attach private packages to the providers or include list list
     */
    protected function attachPrivatePackages()
    {
        if ($this->oldFileName == 'p/providers-latest.json'
            || $this->oldFileName == 'p/provider-latest$%hash%.json'
        ) {
            if ($this->config->getAttachPrivatePackages() === true) {

                $originalFilename = realpath($this->config->getPrivatePackagesFile());
                $content = file_get_contents($originalFilename);
                $data = json_decode($content, true);
                $providers = array();
                foreach ($data['packages'] as $packageName => $packageData) {
                    $providerData = array(
                        'packages' => array(
                            $packageName => $packageData
                        )
                    );
                    $providerContent = json_encode($providerData);
                    switch ($this->shaMethod) {
                        case 'sha1':
                            $hash = sha1($providerContent);
                            break;
                        case 'sha256':
                            $hash = hash('sha256', $providerContent);
                            break;
                    }

                    if (empty($this->providersUrl) === false) {
                        $providerName = $packageName;
                        $filename = str_replace(
                            array('%package%', '%hash%'),
                            array($packageName, $hash),
                            $this->providersUrl
                        );
                    } else {
                        $filename = 'p/' . $packageName . '.json';
                        $providerName = $filename;
                    }

                    $filename = $this->basePath . $filename;
                    $fs = new Filesystem();
                    $fs->mkdir(dirname($filename));
                    file_put_contents($filename, $providerContent);

                    $providers[$providerName] = array(
                        $this->shaMethod => $hash
                    );
                }
                $this->data['providers'] = array_merge($this->data['providers'], $providers);
            }
        }
    }

}
