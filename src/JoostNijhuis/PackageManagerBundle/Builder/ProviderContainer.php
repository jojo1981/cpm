<?php

namespace JoostNijhuis\PackageManagerBundle\Builder;

use Symfony\Component\Filesystem\Filesystem;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface;

/**
 * namespace JoostNijhuis\PackageManagerBundle\Builder\ProviderContainer
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
    public function parse()
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
            $packageContainer->setInputInterface($this->input);
            $packageContainer->parse();

            if ($packageContainer->getHash() != $hash) {
                $this->data['providers'][$oldFileName] = array(
                    $shaMethod => $packageContainer->getHash()
                );
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
        if ($this->config->getAttachPrivatePackages() === true) {
            $attachTo = $this->config->getAttachTo();
            if ($this->oldFileName == $attachTo || $this->fileName == $attachTo) {
                $this->output->writeln('Attaching private packages');
                $providerName = $this->config->getPrivatePackagesProviderName();
                $fileName = realpath($this->config->getPrivatePackagesFile());
                if (file_exists($fileName) === false) {
                    // Maybe throw exception
                    return;
                }
                $content = file_get_contents($fileName);
                switch ($this->shaMethod) {
                    case 'sha1':
                        $hash = sha1($content);
                        break;
                    case 'sha256':
                        $hash = hash('sha256', $content);
                        break;
                }
                $this->data['providers'][$providerName] = array(
                    $this->shaMethod => $hash
                );

                if (empty($this->providersUrl) === false) {
                    $fileName = $this->basePath . str_replace(
                        array('%package%', '%hash%'),
                        array($providerName, $hash),
                        $this->providersUrl
                    );
                } else {
                    $fileName = $this->basePath . 'p/' . $providerName . '.json';
                }
                $fs = new Filesystem();
                $fs->mkdir(dirname($fileName));
                $this->output->writeln('Saving private packages (not parsed) to: ' . $fileName);
                file_put_contents($fileName, $content);
            }
        }
    }

}
