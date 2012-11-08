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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Composer\Package\Version\VersionParser;
use JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler;
use JoostNijhuis\PackageManagerBundle\Packages\PackageFactory;

/**
 * The Packagist Handler class responsible for retrieving
 * all packages data from packagist.org
 */
class PackagistHandler
{

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $parseOnlyStable = true;

    /**
     * @var bool
     */
    protected $enableCache = false;

    /**
     * @var null|\JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler
     */
    protected $privatePackagesHandler;

    /**
     * @var null|CacheHandler
     */
    protected $cacheHandler;

    /**
     * C'tor
     *
     * @param string $url [optional] The url to use for retrieving packages data
     */
    public function __construct($url = null)
    {
        $url = (empty($url) ? 'http://packagist.org' : $url);
        $this->url  = $url;
    }

    /**
     * Enable parse only stable packages to parse only packages
     * which have a stability other than dev, if disable the all
     * packages will be parsed. So the original url will be replaced
     * by one pointing to this application.
     *
     * @param bool $parseOnlyStable
     */
    public function setParseOnlyStable($parseOnlyStable)
    {
        $this->parseOnlyStable = (bool) $parseOnlyStable;
    }

    /**
     * @param $enableCache
     */
    public function setEnableCache($enableCache)
    {
        $this->enableCache = (bool) $enableCache;
    }

    /**
     * Inject a PrivatePackagesHandler instance for attaching
     * private packages to the ones retrieved from packagist.org or the configured
     * url.
     *
     * @param \JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler $privatePackagesHandler
     */
    public function setPrivatePackagesHandler(PrivatePackagesHandler $privatePackagesHandler)
    {
        $this->privatePackagesHandler = $privatePackagesHandler;
    }

    /**
     * Set Cache Handler to use, this is optionally.
     * This class will function properly if not injected
     *
     * @param CacheHandler $cacheHandler
     */
    public function setCacheHandler(CacheHandler $cacheHandler)
    {
        $this->cacheHandler = $cacheHandler;
    }

    /**
     * Get package object by vendor, package name and version.
     * returns false if no package can be found. Try
     * to find the package in all packages retrieved by packagist.org
     * or the configured url with all attached private packages if
     * a PrivatePackagesHandler is injected.
     *
     * @param string $vendor
     * @param string $package
     * @param string $version
     * @return \Composer\Package\Package|bool
     */
    public function getPackage($vendor, $package, $version)
    {
        $arrPackages = $this->getAllPackages();

        if (!isset($arrPackages[$vendor . '/' . $package][$version])) {
            return false;
        }

        $arrPackage = $arrPackages[$vendor . '/' . $package][$version];
        $objPackage = PackageFactory::getPackageObjectFromArray($arrPackage);
        if ($this->parseOnlyStable && $objPackage->isDev()) {
            return false;
        }
        
        return $objPackage;
    }

    /**
     * Get all packages from packagist.org or the configured url by
     * the constructor, also if a PrivatePackagesHandler is injected and
     * has also packages these will be attached.
     *
     * @return array
     */
    public function getAllPackages()
    {
        $arrMainData = json_decode($this->getFileContent('packages.json', false), true);
        $arrPackages = array();

        if (isset($arrMainData['includes'])) {
            foreach ($arrMainData['includes'] as $fileName => $sha1) {
                $arrIncludesData = json_decode($this->getFileContent($fileName, false), true);
                /* Add include packages to the stack an overwrite existing ones */
                $arrPackages = $this->addPackagesToStack($arrIncludesData['packages'], $arrPackages);
            }
        }

        if (isset($arrMainData['providers-includes'])) {
            foreach($arrMainData['providers-includes'] as $fileName => $sha1) {
                $arrData = json_decode($this->getFileContent($fileName, false), true);
                foreach($arrData['providers'] as $providerFileName => $providerSha1) {
                    $arrProviderData = json_decode($this->getFileContent($providerFileName, false), true);
                    /* Add provider packages to the stack an overwrite existing ones */
                    $arrPackages = $this->addPackagesToStack($arrProviderData['packages'], $arrPackages);
                }
            }
        }

        /* Add private configured packages to the stack an overwrite existing ones */
        $arrPackages = $this->addPackagesToStack($arrMainData['packages'], $arrPackages);

        return $arrPackages;
    }

    /**
     * Get file content by filename, try to get it from cache.
     * If not retrievable from cache try to get it by an url.
     * return false if no content can be returned.
     *
     * @param $fileName          The filename (no url) to get the content from
     * @param bool $parse        Must the content be parsed, so the download url will be replaced if necessary
     * @return bool|string       The content or false if no content can be retrieved
     */
    public function getFileContent($fileName, $parse=true)
    {
        $content = false;
        if ($this->cacheHandler instanceof CacheHandler && $this->enableCache) {
            $content = $this->cacheHandler->getFile($fileName);
        }

        if (empty($content) || $content === false) {
            $content = $this->getFileContentWithCurl($this->url . '/' . $fileName);
            if ($content !== false && $this->cacheHandler instanceof CacheHandler && $this->enableCache) {
                $this->cacheHandler->addFile($fileName, $content);
            }
        }

        if ($fileName == 'packages.json' && !empty($content) && $content !== false) {
            $data = json_decode($content, true);
            foreach($data['includes'] as $includeFileName => $sha) {
                $keys = array_keys($sha);
                $shaMethod = $keys[0];
                $content = $this->getFileContent($includeFileName, true);
                if ($shaMethod == 'sha1') {
                    $shaKey = sha1($content);
                } else {
                    $shaKey = hash('sha256', $content);
                }
                $data['includes'][$includeFileName][$shaMethod] = $shaKey;
            }

            foreach($data['providers-includes'] as $providerFileName => $sha) {
                $keys = array_keys($sha);
                $shaMethod = $keys[0];
                $content = $this->getFileContent($providerFileName, true);
                if ($shaMethod == 'sha1') {
                    $shaKey = sha1($content);
                } else {
                    $shaKey = hash('sha256', $content);
                }
                $data['providers-includes'][$providerFileName][$shaMethod] = $shaKey;
            }

            $content = json_encode($data);
        }

        if (strpos($fileName, 'p/providers-') !== false && !empty($content) && $content !== false) {
            $data = json_decode($content, true);
            foreach($data['providers'] as $providerFileName => $sha) {
                $keys = array_keys($sha);
                $shaMethod = $keys[0];
                $content = $this->getFileContent($providerFileName, true);
                if ($shaMethod == 'sha1') {
                    $shaKey = sha1($content);
                } else {
                    $shaKey = hash('sha256', $content);
                }
                $data['providers'][$providerFileName][$shaMethod] = $shaKey;
            }
            $content = json_encode($data);
        }

        if ($fileName == 'packages.json') {
            $content = $this->attachPrivatePackageData($content);
            $data = json_decode($content, true);
            /* if set, private packages will be ignored */
            unset($data['providers-includes']);
            $content = json_encode($data);
        }

        if ($parse == true) {
            $content = $this->parseContent($content);
        }

        return $content;
    }
    
    /**
     * Get a Response object for a JSON file, returns false if the
     * file can not be retrieved from an URL or from the cacheHandler
     * if a cacheHandler is injected
     *
     * @param string $fileName       The filename (not url) to get a response for
     * @param boolean $parse         Must the content be parsed, so the download url will be replaced if necessary
     * @return boolean|\Symfony\Component\HttpFoundation\Response
     */
    public function getResponse($fileName, $parse=true)
    {
        $content = $this->getFileContent($fileName, $parse);
        if ($content === false) {
            return false;
        }
        
        $headers = array(
            'Content-type' => 'application/json'
        );
        
        return new Response($content, 200, $headers);
    }

    public function renewCache($forceRenewWholeCache = false)
    {
        if (!$this->cacheHandler instanceof CacheHandler || $this->enableCache === false) {
            return false;
        }
        $content = $this->getFileContentWithCurl($this->url . '/packages.json');
        $this->cacheHandler->addFile('packages.json', $content);
        if (empty($content) || $content === false) {
            return array();
        }
        $arrMainData = json_decode($content, true);

        $shaToMethod = array(
            'sha1'   => 'getSha1ForFile',
            'sha256' => 'getSha256ForFile'
        );

        if (isset($arrMainData['includes'])) {
            foreach ($arrMainData['includes'] as $fileName => $sha) {
                $keys = array_keys($sha);
                $shaKey = $keys[0];

                $sha_remote = $sha[$shaKey];
                $method = $shaToMethod[$shaKey];

                $sha_cache = $this->cacheHandler->$method($fileName);

                if ($forceRenewWholeCache || $sha_remote != $sha_cache) {
                    $fileContents = $this->getFileContentWithCurl($this->url . '/'. $fileName);
                    if ($this->cacheHandler->addFile($fileName, $fileContents)) {
                        $this->cacheHandler->writeToOutput('Written file: \'' . $fileName . '\' to cache');
                    } else {
                        $this->cacheHandler->writeToOutput('Couldn\'t write file: \'' . $fileName . '\' to cache', true);
                    }
                } else {
                    $this->cacheHandler->writeToOutput(sprintf(
                        'File: \'%s\' has the same sha1 hash: \'%s\' as on: \'%s\' and doesn\'t need to be fetched.',
                        $fileName,
                        $sha_remote,
                        $this->url
                    ));
                }
            }
        }

        if (isset($arrMainData['providers-includes'])) {
            foreach ($arrMainData['providers-includes'] as $providerFileName => $sha) {
                $keys = array_keys($sha);
                $shaKey = $keys[0];

                $sha_remote = $sha[$shaKey];
                $method = $shaToMethod[$shaKey];

                $sha_cache = $this->cacheHandler->$method($providerFileName);

                if ($forceRenewWholeCache || $sha_remote != $sha_cache) {
                    $fileContents = $this->getFileContentWithCurl($this->url . '/'. $providerFileName);
                    if ($this->cacheHandler->addFile($providerFileName, $fileContents)) {
                        $this->cacheHandler->writeToOutput('Written file: \'' . $providerFileName . '\' to cache');
                    } else {
                        $this->cacheHandler->writeToOutput('Couldn\'t write file: \'' . $providerFileName . '\' to cache', true);
                    }
                } else {
                    $fileContents = $this->cacheHandler->getFile($providerFileName);
                    $this->cacheHandler->writeToOutput(sprintf(
                        'File: \'%s\' has the same sha1 hash: \'%s\' as on: \'%s\' and doesn\'t need to be fetched.',
                        $providerFileName,
                        $sha_remote,
                        $this->url
                    ));
                }
                $arrProviderData = json_decode($fileContents, true);
                foreach($arrProviderData['providers'] as $fileName => $sha) {
                    $keys = array_keys($sha);
                    $shaKey = $keys[0];

                    $sha_remote = $sha[$shaKey];
                    $method = $shaToMethod[$shaKey];

                    $sha_cache = $this->cacheHandler->$method($fileName);

                    if ($forceRenewWholeCache || $sha_remote != $sha_cache) {
                        $fileContents = $this->getFileContentWithCurl($this->url . '/'. $fileName);
                        if ($this->cacheHandler->addFile($fileName, $fileContents)) {
                            $this->cacheHandler->writeToOutput('Written file: \'' . $fileName . '\' to cache');
                        } else {
                            $this->cacheHandler->writeToOutput('Couldn\'t write file: \'' . $fileName . '\' to cache', true);
                        }
                    } else {
                        $this->cacheHandler->writeToOutput(sprintf(
                            'File: \'%s\' has the same sha1 hash: \'%s\' as on: \'%s\' and doesn\'t need to be fetched.',
                            $fileName,
                            $sha_remote,
                            $this->url
                        ));
                    }
                }
            }
        }
    }


    /**
     * Pass array with packages and add them to the passed stack.
     * If a package exists already in the stack this one will be
     * replaced, the new stack will be returned
     *
     * @param array $packages
     * @param array $stack
     * @return array
     */
    protected function addPackagesToStack(array $packages, array $stack)
    {
        foreach ($packages as $vendorPackageName => $vendorPackagesData) {
            foreach ($vendorPackagesData as $packageVersion => $packageData) {
                $stack[$vendorPackageName][$packageVersion] = $packageData;
            }
        }

        return $stack;
    }

    /**
     * Attach the private packages retrieved by the injected
     * privatePackagesHandler if injected, if not the original string
     * will be returned.
     *
     * @param string $content         JSON string
     * @return string                 JSON string
     */
    protected function attachPrivatePackageData($content)
    {
        $arrData = json_decode($content, true);
        $arrPrivatePackages = $this->privatePackagesHandler->getPrivatePackages();

        foreach($arrPrivatePackages as $packageName => $packageData) {
            foreach ($packageData as $version => $package) {
                if (isset($package['dist'])) {
                    if ($package['dist']['type'] == 'svn') {
                        $parts = parse_url($package['dist']['url']);
                        $package['dist']['url'] = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? $parts['query'] : '');
                    }
                }
                if (isset($package['source'])) {
                    if ($package['source']['type'] == 'svn') {
                        $parts = parse_url($package['source']['url']);
                        $package['source']['url'] = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? $parts['query'] : '');
                    }
                }
                $packageData[$version] = $package;
            }
            $arrData['packages'][$packageName] = $packageData;
        }

        return json_encode($arrData);
    }

    /**
     * Parse the json content and replaces the package source or
     * dist data. Depending on if $this->parseOnlyStable is true then
     * only the packages with an other stability as dev will be parsed
     * if this setting is false all package will be parsed.
     *
     * @param string $content  JSON string with package data
     * @return string          Parse JSON string
     */
    protected function parseContent($content)
    {
        /* TODO: Make this configurable instead of hard dependency to the Request object */
        $request = Request::createFromGlobals();
        $prefixDownloadUri = $request->getUriForPath('/downloads/');

        $arrData = json_decode($content, true);

        if (isset($arrData['packages'])) {
            $arrRet = $arrData;
            foreach($arrData['packages'] as $packageName => $packageData) {
                foreach($packageData as $version => $data) {

                    $doParse = true;
                    if ($this->parseOnlyStable) {
                        $stability = VersionParser::parseStability($version);
                        $doParse = $stability !== 'dev';
                    }

                    if ($doParse) {
                        $data['dist']['type']      = 'zip';
                        $data['dist']['reference'] = $data['version'];
                        $data['dist']['shasum']    = '';
                        $data['dist']['url']       = $prefixDownloadUri . $packageName . '/' . $version . '.zip';
                        if (isset($data['source'])) {
                            unset($data['source']);
                        }
                    }

                    $arrRet['packages'][$packageName][$version] = $data;
                }
            }
            return json_encode($arrRet);
        }

        return $content;
    }

    /**
     * Get the file content with curl, if succeeded return the
     * file content if not returns false.
     *
     * @param string $url   The url to get the content from
     * @return bool|string  The content or false in case no content can be returned
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
            return false;
        }
        
        return $content;
    }

}
