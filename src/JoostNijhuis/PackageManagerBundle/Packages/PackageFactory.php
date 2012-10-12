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

use Composer\Package\Package;

/**
 * Package factory class, will create a new Package object
 * from an array.
 */
class PackageFactory
{
    
    /**
     * Returns a new Package instance generate from array data
     *
     * @param array $arrPackageData
     * @return \Composer\Package\Package
     */
    public static function getPackageObjectFromArray(array $arrPackageData)
    {
        $objPackage = new Package(
            $arrPackageData['name'],
            $arrPackageData['version'],
            $arrPackageData['version_normalized']
        );

        if (!empty($arrPackageData['source']['type'])) {
            $objPackage->setSourceType($arrPackageData['source']['type']);
        }
        
        if (!empty($arrPackageData['source']['url'])) {
            $objPackage->setSourceUrl($arrPackageData['source']['url']);
        }
        
        if (!empty($arrPackageData['source']['reference'])) {
            $objPackage->setSourceReference($arrPackageData['source']['reference']);
        }
        
        if (!empty($arrPackageData['dist']['type'])) {
            $objPackage->setDistType($arrPackageData['dist']['type']);
        }
        
        if (!empty($arrPackageData['dist']['url'])) {
            $objPackage->setDistUrl($arrPackageData['dist']['url']);
        }
        
        if (!empty($arrPackageData['dist']['reference'])) {
            $objPackage->setDistReference($arrPackageData['dist']['reference']);
        }
        
        if (!empty($arrPackageData['dist']['shasum'])) {
            $objPackage->setDistSha1Checksum($arrPackageData['dist']['shasum']);
        }
        
        if (!empty($arrPackageData['type'])) {
            $objPackage->setType($arrPackageData['type']);
        }

        if (!empty($arrPackageData['target-dir'])) {
            $objPackage->setTargetDir($arrPackageData['target-dir']);
        }
        
        return $objPackage;
    }
    
}