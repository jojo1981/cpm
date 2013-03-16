<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Packages;

/**
 * JoostNijhuis\PackageManagerBundle\Packages\ZipArchive
 *
 * This class is a wrapper around the standard
 * PHP ZipArchive class and add a method for adding
 * a directory to the zip archive file.
 */
class ZipArchive extends \ZipArchive
{

    /**
     * Add a whole directory to the zip archive file, all files and
     * directories in the passed directory will be added into the root
     * of the zip archive file.
     * Throws an exception if the passed directory doesn't exists.
     *
     * @param string $directory
     * @throws \Exception
     */
    public function AddDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new \Exception(sprintf(
                'Directory: \'%s\' is not a directory or doesn\'t exists',
                $directory
            ));
        }
        $directory = realpath($directory);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory), 
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {

            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                continue;
            }
    
            $file = realpath($file);
            if (is_dir($file) === true) {
                $rel_file = (str_replace($directory . DIRECTORY_SEPARATOR, '', $file));
                $this->addEmptyDir($rel_file);
            } else if (is_file($file) === true) {
                $rel_file = (str_replace($directory . DIRECTORY_SEPARATOR, '', $file));
                $this->addFromString($rel_file, file_get_contents($file));
            }
        }
    }
    
}
