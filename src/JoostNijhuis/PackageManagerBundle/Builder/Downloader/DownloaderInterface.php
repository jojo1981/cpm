<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Builder\Downloader;

/**
 * namespace JoostNijhuis\PackageManagerBundle\Builder\Downloader\DownloaderInterface
 */
interface DownloaderInterface
{

    /**
     * Download from path and prefix this path with the base url
     * return downloaded content
     *
     * @param string $path
     * @return string
     */
    public function download($path);

}
