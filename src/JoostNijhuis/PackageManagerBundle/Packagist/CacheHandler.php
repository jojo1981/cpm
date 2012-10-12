<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Packagist;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The cache handler class which can be used by the PackagistHandler
 */
class CacheHandler
{

    /**
     * @var CacheDriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Symfony\Component\Console\Input\InputInterface $input
     */
    protected $input;

    /**
     * @var Symfony\Component\Console\Output\OutputInterface $output
     */
    protected $output;

    /**
     * @var bool $forceRenewWholeCache
     */
    protected $forceRenewWholeCache = false;

    /**
     * Constructor
     *
     * The cache handler can only work when he get an cache driver injected.
     * That's why this must be done in the c'tor
     *
     * @param CacheDriverInterface $driver  The cache driver which will be used for storage
     * @param string|null [optional] $url   default will be: 'http://packagist.org'
     */
    public function __construct(CacheDriverInterface $driver, $url = null)
    {
        $this->driver = $driver;
        $this->url    = (empty($url) ? 'http://packagist.org' : $url);
    }

    /**
     * Set the input interface to use for retrieving arguments and/or
     * options. Can be used if this class is used in a Console Command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return void
     */
    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;
        $this->driver->setInputInterface($input);
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
        $this->driver->setOutputInterface($output);
    }

    /**
     * Set the behavior of the cache renew behavior, if passed true
     * the whole cache will be renewed. If false which is the default,
     * only the files with a different sha1 hash will be renewed.
     * For use with cron task, suggest setting this to false to
     * prevent performance issues, depending on how often the job
     * will be running of course.
     *
     * @param bool $forceRenewWholeCache
     */
    public function setForceRenewWholeCache($forceRenewWholeCache)
    {
        $this->forceRenewWholeCache = $forceRenewWholeCache;
    }

    /**
     * Renew the files in cache
     */
    public function renewCache()
    {
        $files = $this->getFiles();

        if (!empty($files)) {
            $this->writeToOutput('Successfully parsed content from: \'packages.json\'');
            $this->writeToOutput(sprintf(
                'The following files need to be fetched: \'%s\'',
                implode(', ', $files)
            ));
        } else {
            $this->writeToOutput('No files need to be fetched.');
        }

        foreach ($files as $fileName) {
            $fileContents = $this->getFileContentWithCurl($this->url . '/'. $fileName);
            if ($this->driver->addFile($fileName, $fileContents)) {
                $this->writeToOutput('Written file: \'' . $fileName . '\' to cache');
            } else {
                $this->writeToOutput('Couldn\'t write file: \'' . $fileName . '\' to cache', true);
            }
        }
    }

    /**
     * Retrieve all json files from packagist.org if $this->forceRenewWholeCache is true
     * if false only the json files with a different sha1 hash will be returned.
     *
     * @return array containing all or only the changed json files from packagist.org
     */
    protected function getFiles()
    {
        $files = array();
        $this->writeToOutput('Try to get content from: \'' . $this->url . '/packages.json' . '\'');
        $content = $this->getFileContentWithCurl($this->url . '/packages.json');

        if ($content !== false) {
            $arrMainData = json_decode($content, true);
            foreach ($arrMainData['includes'] as $fileName => $sha1) {
                $sha1_remote = $sha1['sha1'];
                $sha1_cache = $this->driver->getSha1ForFile($fileName);

                if ($sha1_cache !== false) {
                    $sha1_cache = $sha1_remote;
                }

                if (!$this->forceRenewWholeCache && $sha1_remote == $sha1_cache) {
                    $this->writeToOutput(sprintf(
                        'File: \'%s\' has the same sha1 hash: \'%s\' as on: \'%s\' and doesn\'t need to be fetched.',
                        $fileName,
                        $sha1_remote,
                        $this->url
                    ));
                } else {
                    $files[] = $fileName;
                }
            }
        } else {
            $message = sprintf(
                'Couldn\'t retrieve content from: \'%s\'',
                $this->url . '/packages.json'
            );
            $this->writeToOutput($message, true);
            exit;
        }

        if (!empty($files)) {
            $files = array_merge(array('packages.json'), $files);
        }

        return $files;
    }

    /**
     * Get file content from the cache storage, this will be delegated to the
     * injected Driver instance.
     * Returns false if the file can not be found in the cache storage.
     *
     * @param string $fileName
     * @return false|string
     */
    public function getFile($fileName)
    {
        return $this->driver->getFileContent($fileName);
    }

    /**
     * Add a file to the cache storage, this will be delegated to the
     * injected Driver instance.
     *
     * @param string $fileName
     * @param string $fileContent
     * @return bool|int
     */
    public function addFile($fileName, $fileContent)
    {
        return $this->driver->addFile($fileName, $fileContent);
    }

    /**
     * Clean the cache, this will be delegated to the
     * injected Driver instance.
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->driver->cleanCache();
    }

    /**
     * Get the file content from the passed url.
     * If the content can not be retrieved, for example
     * because of a network problem or something else
     * this method returns false
     *
     * @param string $url
     * @return bool|mixed
     */
    protected function getFileContentWithCurl($url)
    {
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        // grab URL and pass it to the browser
        $content = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status != 200) {
            $message = sprintf(
                'The url: \'%s\' can not be retrieved.',
                $url
            );
            $this->writeToOutput($message);
            return false;
        }

        $this->writeToOutput(sprintf(
            'Successfully retrieved content from: \'%s\'',
            $url
        ));

        return $content;
    }

    /**
     * Let the output handler write a writeln
     * if force is true, the text will be written to the output handler
     * even if the intput handler has the option quiet set to true.
     * There will be toggle with the verbosity level to force output.
     * Use force in case of an error and you want to force the error to
     * be written by the output handler.
     * This is handy for cron tasks, the CRON Daemon only expects output in case
     * of an error. The default behavior of the CRON Daemon is to send an email
     * to the system admin with the output as email content.
     *
     * @param string $text
     * @param bool $force
     */
    protected function writeToOutput($text, $force = false)
    {
        if (isset($this->output)) {
            if ($force) {
                $oldVerbose = $this->output->getVerbosity();
                $this->output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
            }

            $this->output->writeln($text);

            if ($force) {
                $this->output->setVerbosity($oldVerbose);
            }
        }
    }

}
