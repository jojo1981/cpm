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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JoostNijhuis\PackageManagerBundle\Packagist\PackagistHandler;
use JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler;

/**
 * JoostNijhuis\PackageManagerBundle\Controller\PackagesController
 *
 * Controller for retrieving the json files.
 */
class PackagesController extends Controller
{

    /**
     * @Route("/{directory1}/{directory2}/{directory3}/{file}.json")
     * @Route("/{directory1}/{directory2}/{file}.json")
     * @Route("/{directory1}/{file}.json")
     * @Route("/{file}.json")
     *
     * Packages index action, this action is responsible for returning
     * parsed json files. Files which exists on packagist.org like packages.json
     *
     * @param Request $request
     * @param string $file
     * @param null|string $directory1
     * @param null|string $directory2
     * @param null|string $directory3
     * @throws NotFoundHttpException
     * @return Response
     */
    public function indexAction(
        Request $request,
        $file = '',
        $directory1 = null,
        $directory2 = null,
        $directory3 = null
    ) {
        $file .= '.json';
        switch (true){
            case (!empty($directory3)):
                $file = $directory3 . '/'  . $file;
            case (!empty($directory2)):
                $file = $directory2 . '/'  . $file;
            case (!empty($directory1)):
                $file = $directory1 . '/'  . $file;
        }

        /* @var PackagistHandler $objPackagistHandler */
        $objPackagistHandler = $this->get('joost_nijhuis_package_manager_packagist_handler');
        $response = $objPackagistHandler->getResponse($file);
        if ($response === false) {
            throw $this->createNotFoundException($request->getRequestUri() . ' Not found');
        }

        return $response;
    }

}
