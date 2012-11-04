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

use Symfony\Component\Finder\Finder;

/**
 * A Simple File Cache Driver which can be injected into a CacheHandler instance
 */
class CacheDriverFile extends CacheDriverAbstract
{

    /**
     * @var string $cacheDir The directory in where the cache files will be saved
     */
    protected $cacheDir;

    /**
     * @param string $cacheDir The directory in where the cache files will be saved
     */
    public function __construct($cacheDir)
    {
        if (!is_dir($cacheDir)) {
            throw new \Exception(sprintf(
                'Directory: \'%s\' doesn\'t exists.',
                $cacheDir
            ));
        }

        $this->cacheDir = $cacheDir;
    }

    /**
     * Add a file to the cache storage and return the amount of bytes written
     * or false when failed to write the file into the cache storage
     *
     * @param string $fileName
     * @param string $fileContent
     * @return int|boolean
     */
    public function addFile($fileName, $fileContents)
    {
        $fileName = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;

        $dir = pathinfo($fileName, PATHINFO_DIRNAME);
        if (is_dir($dir) === false) {
            mkdir($dir, 0777, true);
        }

        $fileHandle = fopen($fileName, 'w+', false);
        $result = fwrite($fileHandle, $fileContents);
        if ($result === false) {
            $message = sprintf(
                'Couldn\'t write file: \'%s\' to the filesystem',
                $fileName
            );
            $this->writeToOutput($message, true);
        } else {
            $message = sprintf(
                'Successfully written file: \'%s\' to the filesystem',
                $fileName
            );
            $this->writeToOutput($message);
        }
        fclose($fileHandle);

        return $result;
    }

    /**
     * Retrieve the content of a file by it's filename
     * return false if no file is found.
     *
     * @param string $fileName
     * @return bool|string
     */
    public function getFileContent($fileName)
    {
        $fileName = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($fileName)) {
            $message = sprintf(
                'Couldn\t read file: \'%s\' from the filesystem',
                $fileName
            );
            $this->writeToOutput($message);
        } else {
            return file_get_contents($fileName);
        }

        return false;
    }

    public function getSha1ForFile($fileName)
    {
        $retVal = false;
        $fileName = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($fileName)) {
            $retVal = sha1_file($fileName);
        }
        
        if ($retVal === false) {
            $message = sprintf(
                'Couldn\'t read sha1 hash for file: \'%s\' from the filesystem',
                $fileName
            );
            $this->writeToOutput($message);
        }

        return $retVal;
    }

    public function cleanCache()
    {
        $files = Finder::create()
            ->ignoreVCS(true)
            ->files()
            ->name('*.json')
            ->in($this->cacheDir)
        ;

        foreach ($files as $fileName) {
            unlink($fileName);
            $message = sprintf(
                'Successfully deleted file: \'%s\' from the filesystem',
                $fileName
            );
            $this->writeToOutput($message);
        }

        $this->writeToOutput('Successfully cleanup the cache');
    }

}
