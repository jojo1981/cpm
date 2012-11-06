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
 * The interface for all the cache drivers which can be injected
 * into a CacheHandler instance
 *
 * @author Joost Nijhuis <jnijhuis81@gmail.com>
 */
interface CacheDriverInterface
{

    /**
     * Add a file to the cache storage
     *
     * @param $fileName string
     * @param $fileContents string
     * @return int|boolean  return false if no data can be stored or
     *                      the number of bytes written to cache
     */
    public function addFile($fileName, $fileContents);

    /**
     * Get the file content by it's filename
     *
     * @param $fileName string The filename to lookup
     * @return string|false    returns false if file can not be found
     */
    public function getFileContent($fileName);

    /**
     * Returns the sha1 hash for the data stored by a filename
     *
     * @param $fileName string The filename to lookup
     * @return string:bool     returns false if no sha1 hash can be found for
     *                         the filename
     */
    public function getSha1ForFile($fileName);

    /**
     * Returns the sha256 hash for the data stored by a filename
     *
     * @param $fileName string The filename to lookup
     * @return string:bool     returns false if no sha1 hash can be found for
     *                         the filename
     */
    public function getSha256ForFile($fileName);

    /**
     * Clear the whole cache.
     *
     * @return void
     */
    public function cleanCache();

    /**
     * Set the output handler to use for handling output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function setOutputInterface(OutputInterface $output);

    /**
     * Set the input handler to use for reading input
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return void
     */
    public function setInputInterface(InputInterface $input);

}
