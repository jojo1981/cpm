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
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Repository\CompositeRepository;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Version\VersionParser;
use Composer\DependencyResolver\Pool;
use Composer\Package\CompletePackage;
use JoostNijhuis\PackageManagerBundle\Composer\Repository\PrivatePackageRepository;
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config;
use JoostNijhuis\PackageManagerBundle\Packages\DownloadHandler;

/**
 * JoostNijhuis\PackageManagerBundle\Controller\DownloadsController
 */
class DownloadsController extends Controller
{
    /**
     * @Route("/downloads/{vendor}/{package}/{file}")
     */
    public function indexAction(Request $request, $vendor, $package, $file)
    {
        $tmpDir = $this->container->getParameter(
            'joost_nijhuis_package_manager.tmp_dir'
        );
        apache_setenv("HOME", $tmpDir);

        $version  = pathinfo($file, PATHINFO_FILENAME);
        $search   = $vendor . '/' . $package;

        $response   = false;
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

        /** @var Config $parseConfig */
        $parseConfig = $this->get('joost_nijhuis_package_manager.config');

        $config = $parseConfig->getComposerConfig();
        $io = new NullIO();
        $composer = Factory::create($io, $config);

        $privatePackagesFile = realpath(
            $this->container->getParameter(
                'private_packages_output_file'
            )
        );

        $privatePackageRepository = new PrivatePackageRepository(
            $privatePackagesFile
        );
        $objPackage = $privatePackageRepository->findPackage(
            $search,
            $versionNormalized
        );

        if ($objPackage === null) {
            $repositories = new CompositeRepository(
                $composer->getRepositoryManager()->getRepositories()
            );

            $pool = new Pool('alpha');
            $pool->addRepository($repositories);

            $matches = $pool->whatProvides($search, $constraint);
            /** @var CompletePackage $package */
            foreach ($matches as $index => $package) {
                if ($package->getVersion() === $versionNormalized) {
                    $objPackage = $package;
                    break;
                }
            }
        }

        if ($objPackage) {
            /** @var DownloadHandler $downloadHandler */
            $downloadHandler = $this->get(
                'joost_nijhuis_package_manager_download_handler'
            );
            $response = $downloadHandler->getDownloadResponse(
                $objPackage,
                $version
            );
        }
        
        if ($response === false) {
            throw $this->createNotFoundException(
                $request->getRequestUri() . ' Not found'
            );
        }

        return $response;
    }
}
