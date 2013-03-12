<?php

namespace JoostNijhuis\PackageManagerBundle\Builder;

use Symfony\Component\Filesystem\Filesystem;
use JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\PackageContainer
 *
 * This object is hard linked to a JSON file which contains
 * packages
 */
class PackageContainer extends JsonFile
{

    /**
     * @var string
     */
    protected $providersUrl = '';

    /***
     * @var string
     */
    protected $packageName = '';

    /**
     * Constructor
     *
     * @param string $fileName
     * @param string $basePath
     * @param string $shaMethod
     * @param string $providersUrl [optional]
     * @param string $packageName [optional]
     * @param Downloader\DownloaderInterface $downloader [optional]
     */
    public function __construct(
        $fileName,
        $basePath,
        $shaMethod,
        $providersUrl = '',
        $packageName = '',
        DownloaderInterface $downloader = null
    ) {
        $this->setDownloader($downloader);
        $this->setData($fileName, $basePath);

        $this->shaMethod = $shaMethod;
        $this->providersUrl = $providersUrl;
        $this->packageName = $packageName;
    }

    /**
     * {@inheritDoc}
     */
    public function parse()
    {
        $this->parsePackages();
        $this->writeFileToDisc();
    }

    /**
     * Will only be triggered if parsing was needed.
     * The content will be encoded to JSON and save to the
     * current filename, if needed the file will be renamed
     */
    protected function writeFileToDisc()
    {
        if (empty($this->providersUrl) === false) {
            $oldFileName = $this->fileName;
            $hash = $this->getHash();
            $this->fileName = $this->basePath . str_replace(
                array('%package%', '%hash%', '/'),
                array($this->packageName, $hash, DIRECTORY_SEPARATOR),
                $this->providersUrl
            );

            $fs = new Filesystem();
            $fs->remove($oldFileName);
            $this->output->writeln('packages file removed from disc: ' . $oldFileName);
            $fs->mkdir(dirname($this->fileName));
        }

        file_put_contents($this->fileName, json_encode($this->data));
        $this->output->writeln('packages file saved to disc: ' . $this->fileName);
    }

}
