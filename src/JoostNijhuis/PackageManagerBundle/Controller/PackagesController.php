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
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config;
use JoostNijhuis\PackageManagerBundle\Builder\File\PackageContainer;

/**
 * JoostNijhuis\PackageManagerBundle\Controller\PackagesController
 *
 * Controller for retrieving the json files.
 */
class PackagesController extends Controller
{
    /**
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
     * @throws NotFoundHttpException
     * @return Response
     */
    public function indexAction(
        Request $request,
        $file = '',
        $directory1 = null,
        $directory2 = null
    ) {
        $content = null;
        $file .= '.json';
        switch (true){
            case (!empty($directory2)):
                $file = $directory2 . DIRECTORY_SEPARATOR  . $file;
            case (!empty($directory1)):
                $file = $directory1 . DIRECTORY_SEPARATOR  . $file;
        }

        /** @var Config $config */
        $config = $this->get('joost_nijhuis_package_manager.config');
        if ($config->getEnablePackagistProxy() == false) {
            if ($file == 'packages.json') {
                $fileName = $config->getPrivatePackagesFile();
                if ($config->getParse()) {
                    $PackageContainer = new PackageContainer($fileName, dirname($fileName), 'sha265');
                    $PackageContainer->setConfig($config);
                    $PackageContainer->parse(false);
                    $data = $PackageContainer->getData();
                    $content = json_encode($data);
                } else {
                    return file_get_contents($fileName);
                }
            }
        } else {
            $indexDir = $config->getIndexPath();
            $indexDir = realpath($indexDir) . DIRECTORY_SEPARATOR;
            $fileName = $indexDir . $file;
            if (file_exists($fileName)) {
                $content = file_get_contents($fileName);
            }
        }

        $response = false;
        if ($content) {
            $headers = array(
                'Content-type' => 'application/json'
            );
            $response = new Response($content, 200, $headers);
        }

        if ($response === false) {
            throw $this->createNotFoundException($request->getRequestUri() . ' Not found');
        }

        return $response;
    }
}
