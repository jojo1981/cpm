<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JoostNijhuis\PackageManagerBundle\Packagist\PackagistHandler;
use JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler;

class DownloadsController extends Controller
{
    
    /**
     * @Route("/downloads/{vendor}/{package}/{file}")
     */
    public function indexAction(Request $request, $vendor, $package, $file)
    {
        $response = false;
        $version = pathinfo($file, PATHINFO_FILENAME);

        /* @var \JoostNijhuis\PackageManagerBundle\Packagist\PackagistHandler $objPackagistHandler */
        $objPackagistHandler = $this->get('joost_nijhuis_package_manager_packagist_handler');
        $objPackage = $objPackagistHandler->getPackage($vendor, $package, $version);

        if ($objPackage !== false) {
            $downloadHandler = $this->get('joost_nijhuis_package_manager_download_handler');
            $response = $downloadHandler->getDownloadResponse($objPackage);
        }
        
        if ($response === false) {
            throw $this->createNotFoundException($request->getRequestUri() . ' Not found');
        }

        return new Response($response);
    }

}
