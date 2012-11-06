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
     * @var packagistHandler
     */
    protected $packagistHandler;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface $input
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface $output
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
    public function __construct(CacheDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Set the packagistHandler to use for retrieving the content to
     * renew the cache
     *
     * @param PackagistHandler $packagistHandler
     * @return void
     */
    public function setPackagistHandler(PackagistHandler $packagistHandler)
    {
        $this->packagistHandler = $packagistHandler;
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
     * Get file content from the cache storage, this will be delegated to the
     * injected Driver instance.
     * Returns false if the file can not be found in the cache storage.
     *
     * @param string $fileName
     * @return false|string
     */
    public function getFile($fileName)
    {
        $this->check();
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
        $this->check();
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
        $this->check();
        $this->driver->cleanCache();
    }

    /**
     * Returns the sha256 hash for the data stored by a filename
     *
     * @param $fileName string The filename to lookup
     * @return string:bool     returns false if no sha1 hash can be found for
     *                         the filename
     */
    public function getSha256ForFile($fileName)
    {
        return $this->driver->getSha256ForFile($fileName);
    }

    /**
     * Returns the sha1 hash for the data stored by a filename
     *
     * @param $fileName string The filename to lookup
     * @return string:bool     returns false if no sha1 hash can be found for
     *                         the filename
     */
    public function getSha1ForFile($fileName)
    {
        return $this->driver->getSha1ForFile($fileName);
    }

    /**
     * Renew all the files in cache, only the ones who need to
     * be renewed or all files if forceRenewWholeCache is set to true
     *
     * @return void
     */
    public function renewCache()
    {
        $this->check();
        $this->packagistHandler->renewCache($this->forceRenewWholeCache);
    }

    /**
     * Let the output handler write a writeln
     * if force is true, the text will be written to the output handler
     * even if the input handler has the option quiet set to true.
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
    public function writeToOutput($text, $force = false)
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

    /**
     * Return true if this cache handler can work, only when a packagistHandler
     * is injected.
     *
     * @return bool
     * @throws \RuntimeException
     */
    protected function check()
    {
        if (!isset($this->packagistHandler)) {
            throw new \RuntimeException(
                'No packagist handler set, so this cache handler can not work'
            );
        }

        return true;
    }

}
