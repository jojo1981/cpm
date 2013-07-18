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
 * JoostNijhuis\PackageManagerBundle\Builder\Downloader\Downloader
 */
class Downloader implements DownloaderInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Constructor
     *
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * Download from path and prefix this path with the base url
     * return downloaded content
     *
     * @param string $path
     * @return string
     */
    public function download($path)
    {
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        $url = $this->baseUrl . $path;

        return $this->getContentWithCurl($url);
    }

    /**
     * Get the file content with curl, if succeeded return the
     * file content if not returns false.
     *
     * @param string $url   The url to get the content from
     * @return bool|string  The content or false in case no content can be returned
     */
    protected function getContentWithCurl($url)
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
            return false;
        }

        return $content;
    }
}
