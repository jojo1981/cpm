<?php

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
