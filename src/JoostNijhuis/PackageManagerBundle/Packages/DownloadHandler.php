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

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class can be used for downloading packages.
 */
class DownloadHandler
{

    /**
     * @var string
     */
    protected $temp_dir;

    /**
     * @var string
     */
    protected $package_dir;

    /**
     * @var \JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication
     */
    protected $svnAuthentication;

    /**
     * Constructor
     *
     * @param string $package_dir              The directory to save the downloaded packages to
     * @param null|string $temp_dir [optional] The temporarily directory to use, if null the system default will be used
     */
    public function __construct($package_dir, $temp_dir = null)
    {
        $this->setPackageDirectory($package_dir);
        if ($temp_dir === null) {
            $temp_dir = sys_get_temp_dir();
        }
        $this->setTempDirectory($temp_dir);
    }

    /**
     * Inject the SvnAuthentication helper class.
     * If injected this will be used to find user credentials for
     * the svn url's.
     *
     * @param \JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication $svnAuthentication
     * @return void
     */
    public function setSvnAuthentication(SvnAuthentication $svnAuthentication)
    {
        $this->svnAuthentication = $svnAuthentication;
    }

    /**
     * Download the package and save it to the configured packages directory.
     * The package will be retrieved by the source/dist type and saved into
     * the configured temporarily directory. Then all the package files will be
     * added to a new Zip archive which will be placed into the configured
     * package directory. The directory structure relative from the package download
     * directory will be: ./vendor_name/package_name/version.zip
     *
     * Returns the absolute path of the downloaded file which has been saved
     * as a Zip archive file.
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    protected function download(PackageInterface $package)
    {
        $fs = new Filesystem();
        
        $package_dir = $this->package_dir . DIRECTORY_SEPARATOR .
            str_replace('/', DIRECTORY_SEPARATOR, $package->getName());
        $package_file = $package_dir . DIRECTORY_SEPARATOR . $package->getVersion() . '.zip';

        $fs->ensureDirectoryExists($package_dir);

        if (!file_exists($package_file)) {

            $tmpDirPackage = $this->temp_dir . DIRECTORY_SEPARATOR . str_replace('/', '_', $package->getName());
            $fs->removeDirectory($tmpDirPackage);

            $objDownload = $this->getDownloader($package);
            $objDownload->download($package, $tmpDirPackage);
            
            $objZipArchive = new ZipArchive();
            $objZipArchive->open($package_file, ZIPARCHIVE::CREATE);
            $objZipArchive->AddDirectory($tmpDirPackage);
            $objZipArchive->close();
            
            $fs->removeDirectory($tmpDirPackage);
        }
        return $package_file;
    }

    /**
     * Returns false if no download file can be downloaded and not found
     * in the packages download directory. When the file can be found this
     * method will return a Response object which can be returned into a
     * controller action.
     *
     * @param \Composer\Package\PackageInterface $package
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function getDownloadResponse(PackageInterface $package)
    {
        $package_file = $this->download($package);
        
        if (!file_exists($package_file)) {
            return false;
        }
        
        $fileName      = pathinfo($package_file, PATHINFO_FILENAME);
        $fileExtention = pathinfo($package_file, PATHINFO_EXTENSION);
        $fileName      = $fileName . '.' . $fileExtention;
        $fileSize      = filesize($package_file);
        $fileContent   = file_get_contents($package_file);
        $fileMimeType  = mime_content_type($package_file);

        $headers = array(
            'Pragma'                    => 'public', 
            'Expires'                   => '0', 
            'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0', 
            'Cache-Control'             => 'public', 
            'Content-Description'       => 'File Transfer', 
            'Content-type'              => $fileMimeType, 
            'Content-Disposition'       => 'attachment; filename="' . $fileName . '"', 
            'Content-Transfer-Encoding' => 'binary', 
            'Content-Length'            => $fileSize
        );

        $response = new Response($fileContent, 200, $headers);
        $response->send();
        return $response;
    }

    /**
     * Returns a Downloader object, depending on the package
     * dist/source type: svn, git, zip etc...
     *
     * @param \Composer\Package\PackageInterface $package
     * @return \Composer\Downloader\DownloaderInterface|SvnDownloader
     */
    protected function getDownloader(PackageInterface $package)
    {
        $type = ($package->getDistType() != '' ? $package->getDistType() : $package->getSourceType());
        $io = new NullIO();
        $config = Factory::createConfig();

        if ($type == 'svn') {
            $downloader = new SvnDownloader($io, $config);
            if (isset($this->svnAuthentication)) {
                $url = ($package->getDistUrl() != '' ? $package->getDistUrl() : $package->getSourceUrl());
                $creds = $this->svnAuthentication->getCredentialsForUrl($url);
                if ($creds !== false) {
                    $downloader->setUsername($creds['username']);
                    $downloader->setPassword($creds['password']);
                }
            }
        } else {
            $factory = new Factory();
            $downloadManager = $factory->createDownloadManager($io, $config);
            $downloader = $downloadManager->getDownloader($type);
        }

        return $downloader;
    }

    /**
     * Set the temporarily directory to use and checks if this
     * directory exists
     *
     * @param string $directory
     * @return void
     * @throws \Exception
     */
    protected function setTempDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new \Exception(sprintf(
            'Temp directory: \'%s\' doesn\'t exists',
            $directory
            ));
        }
        $this->temp_dir = $directory;
    }

    /**
     * Set the packages directory to use and checks if this
     * directory exists, the packages directory is the directory
     * where the downloaded packages will be saved.
     *
     * @param $directory
     * @return void
     * @throws \Exception
     */
    protected function setPackageDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new \Exception(sprintf(
            'Package directory: \'%s\' doesn\'t exists',
            $directory
            ));
        }
        $this->package_dir = $directory;
    }

}