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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JoostNijhuis\PackageManagerBundle\Packagist\PackagistHandler;
use JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler;

class PackagesController extends Controller
{
    
    /**
     * @Route("/{directory}/{file}.json")
     * @Route("/{file}.json")
     * 
     * Packages index action, this action is responsible for returning
     * parsed json files. Files which exists on packagist.org like packages.json
     * 
     * @param Symfony\Component\HttpFoundation\Request $request   contains the request object automaticly injected by the dispatcher
     * @param string $file                                        contains the file without extention to use
     * @param string $directory [optional]                        The directory if passed
     * @return Symfony\Component\HttpFoundation\Response
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request, $file = '', $directory = null)
    {
        $file .= '.json';
        if (!empty($directory)) {
            $file = $directory . '/'  . $file;
        }

        /* @var \JoostNijhuis\PackageManagerBundle\Packagist\PackagistHandler $objPackagistHandler */
        $objPackagistHandler = $this->get('joost_nijhuis_package_manager_packagist_handler');
        $response = $objPackagistHandler->getResponse($file);
        if ($response === false) {
            throw $this->createNotFoundException($request->getRequestUri() . ' Not found');
        }

        return $response;
    }

}
