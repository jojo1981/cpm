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

class PackagistHandler
{
    
    protected $url;
    
    protected $parseOnlyStable = true;

    protected $enableCache = false;
    
    protected $privatePackagesHandler;

    protected $cacheHandler;

    public function __construct($url = null)
    {
        $url = (empty($url) ? 'http://packagist.org' : $url);
        $this->url  = $url;
    }
    
    public function setParseOnlyStable($parseOnlyStable)
    {
        $this->parseOnlyStable = (bool) $parseOnlyStable;
    }

    public function setEnableCache($enableCache)
    {
        $this->enableCache = $enableCache;
    }
    
    public function setPrivatePackagesHandler(PrivatePackagesHandler $privatePackagesHandler)
    {
        $this->privatePackagesHandler = $privatePackagesHandler;
    }

    public function setCacheHandler(CacheHandler $cacheHandler)
    {
        $this->cacheHandler = $cacheHandler;
    }

    /**
     * @param string $vendor
     * @param string $package
     * @param string $version
     * @return \Composer\Package\MemoryPackage|bool
     */
    public function getPackage($vendor, $package, $version)
    {
        $arrPackages = $this->getAllPackages();

        if (!isset($arrPackages[$vendor . '/' . $package][$version])) {
            return false;
        }

        $arrPackage  = $arrPackages[$vendor . '/' . $package][$version];
        $objPackage  = PackageFactory::getPackageObjectFromArray($arrPackage);
        if ($this->parseOnlyStable && $objPackage->isDev()) {
            return false;
        }
        
        return $objPackage;
    }
    
    public function getAllPackages()
    {
        $arrMainData = json_decode($this->getFileContent('packages.json', false), true);
        $arrPackages = $arrMainData['packages'];
        foreach ($arrMainData['includes'] as $fileName => $sha1) {
            $arrData = json_decode($this->getFileContent($fileName, false), true);
            $arrPackages = array_merge_recursive($arrData['packages'], $arrPackages);
        }
        
        return $arrPackages;
    }
    
    public function getFileContent($fileName, $parse=true)
    {
        $content = false;
        if ($this->cacheHandler instanceof CacheHandler && $this->enableCache) {
            $content = $this->cacheHandler->getFile($fileName);
        }

        if ($content === false) {
            $content = $this->getFileContentWithCurl($this->url . '/' . $fileName);
            if ($content !== false && $this->cacheHandler instanceof CacheHandler && $this->enableCache) {
                $this->cacheHandler->addFile($fileName, $content);
            }
        }
        
        if ($content !== false 
            && $fileName == 'packages.json'
            && $this->privatePackagesHandler instanceof PrivatePackagesHandler) {
            $content = $this->attachPrivatePackageData($content);
        }

        if ($content !== false && $parse === true) {
            $content = $this->parseContent($content);
        }

        return $content;
    }
    
    /**
     * @param string $fileName
     * @param boolean $parse
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
    
    protected function attachPrivatePackageData($content)
    {
        $arrData = json_decode($content, true);
        $arrPrivatepackages = $this->privatePackagesHandler->getPrivatePackages();

        foreach($arrPrivatepackages as $packageName => $packageData) {

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
            $data = array('packages' => array(
                $packageName => $packageData
            ));
            $arrData = array_merge($arrData, $data);
        }

        return json_encode($arrData);
    }
    
    protected function parseContent($content)
    {
        $request = Request::createFromGlobals();
        $prefixDownloadUri = $request->getUriForPath('/downloads/');

        $arrData = json_decode($content, true);
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
