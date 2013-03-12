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
use Symfony\Component\Filesystem\Filesystem;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\CompositeRepository;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Version\VersionParser;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\Package\CompletePackage;

class DownloadsController extends Controller
{
    
    /**
     * @Route("/downloads/{vendor}/{package}/{file}")
     */
    public function indexAction(Request $request, $vendor, $package, $file)
    {
        $version  = pathinfo($file, PATHINFO_FILENAME);
        $search   = $vendor . '/' . $package;

        $response   = false;
        $objPackage = false;
        $constraint = null;

        if ($version) {
            $versionParser = new VersionParser();
            try {
                $versionNormalized = $versionParser->normalize($version);
                $constraint = new VersionConstraint('=', $versionNormalized);
            } catch (\UnexpectedValueException $e) {
                $versionNormalized = $version;
            }
        }

        $config = $this->getConfig();
        $io = new NullIO();
        $composer = Factory::create($io, $config);

        /**
         * create new private packages repository from parsed json file
         * add array merge
         */
        $repositories = new CompositeRepository($composer->getRepositoryManager()->getRepositories());

        $policy = new DefaultPolicy();
        $pool = new Pool();
        $pool->addRepository($repositories);

        $matches = $pool->whatProvides($search, $constraint);
        /** @var CompletePackage $package */
        foreach ($matches as $index => $package) {
            if ($package->getVersion() === $versionNormalized) {
                $objPackage = $package;
                break;
            }
        }

        if ($objPackage !== false) {
            $downloadHandler = $this->get('joost_nijhuis_package_manager_download_handler');
            $response = $downloadHandler->getDownloadResponse($objPackage);
        }
        
        if ($response === false) {
            throw $this->createNotFoundException($request->getRequestUri() . ' Not found');
        }

        return $response;
    }

    /**
     * Return composer config array, overwrite default config
     *
     * @return array
     */
    protected function getConfig()
    {
        $env = $this->get('kernel')->getEnvironment();
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $cacheDir = $rootDir . '/cache/' . $env . '/composer';

        $cacheFilesDir = $cacheDir . '/files';
        $cacheRepoDir  = $cacheDir . '/repo';
        $cacheVcsDir   = $cacheDir . '/vcs';

        $fs = new Filesystem();
        $fs->mkdir(array($cacheFilesDir, $cacheRepoDir, $cacheVcsDir));

        return array (
            'config' => array(
                'bin-dir'             => 'bin',
                'cache-dir'           => $cacheDir,
                'cache-files-dir'     => $cacheFilesDir,
                'cache-repo-dir'      => $cacheRepoDir,
                'cache-vcs-dir'       => $cacheVcsDir,
                'home'                => $cacheDir
            )
        );
    }

}
