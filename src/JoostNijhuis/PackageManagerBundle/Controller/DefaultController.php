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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler;

/**
 * JoostNijhuis\PackageManagerBundle\Controller\DefaultController
 */
class DefaultController extends Controller
{

    /**
     * @Route("/")
     */
    public function defaultAction()
    {
        $params = array(
            '_locale' => $this->getRequest()->getLocale()
        );

        return $this->redirect(
            $this->generateUrl('joostnijhuis_packagemanager_default_index', $params)
        );
    }
    
    /**
     * @Route("/{_locale}/index.html")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var PrivatePackagesHandler $objPrivatePackagesHandler */
        $objPrivatePackagesHandler = $this->get(
            'joost_nijhuis_package_manager_private_packages_handler'
        );
        $arrPackageData = $objPrivatePackagesHandler->getDataForTemplate();

        $json_example = '{
    "repositories": [ 
        {
            "packagist": false
        },  
        {
            "type": "composer", 
            "url": "' . $request->getUriForPath('') . '"
        }
    ]
}';

        return array(
            'arrPackages'  => $arrPackageData, 
            'json_example' => $json_example, 
        );
    }

}
