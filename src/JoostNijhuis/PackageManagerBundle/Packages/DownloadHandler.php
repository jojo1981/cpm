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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Composer\Downloader\DownloaderInterface;
use Symfony\Component\HttpFoundation\Response;
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config;

/**
 * JoostNijhuis\PackageManagerBundle\Packages\DownloadHandler
 *
 * This class can be used for downloading packages.
 */
class DownloadHandler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $temp_dir;

    /**
     * @var string
     */
    protected $package_dir;

    /**
     * @var SvnAuthentication
     */
    protected $svnAuthentication;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $temp_dir = $config->getTmpPath();
        $package_dir = $config->getPackageDataDirectory();

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
     * @param SvnAuthentication $svnAuthentication
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
     * @param PackageInterface $package
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

            $this->cleanup($tmpDirPackage);

            $objZipArchive = new ZipArchive();
            $objZipArchive->open($package_file, ZIPARCHIVE::CREATE);
            $objZipArchive->AddDirectory($tmpDirPackage);
            $objZipArchive->close();

            $fs->removeDirectory($tmpDirPackage);
        }

        return $package_file;
    }

    /**
     * Remove svn, git, hg etc.. files from passed directory
     *
     * @param string $directory
     */
    protected function cleanup($directory)
    {
        $fs = new Filesystem();

        $finder = new Finder();
        $finder
            ->in($directory)
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
        ;
        $this->addFilesToDeleteToFinder($finder);

        $found = array();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $found[] = $file->getRealPath();
        }

        foreach ($found as $fileName) {
            if (file_exists($fileName)) {
                $fs->remove($fileName);
            }
        }
    }

    /**
     * @param Finder $finder
     */
    protected function addFilesToDeleteToFinder(Finder $finder)
    {
        foreach ($this->getFilesToDelete() as $file) {
            $finder->name($file);
        }
    }

    /**
     * @return array
     */
    protected function getFilesToDelete()
    {
        return array(
            '.svn',
            '_svn',
            'CVS',
            '_darcs',
            '.arch-params',
            '.monotone',
            '.bzr',
            '.git',
            '.gitkeep',
            '.gitignore',
            '.hg'
        );
    }

    /**
     * Returns false if no download file can be downloaded and not found
     * in the packages download directory. When the file can be found this
     * method will return a Response object which can be returned into a
     * controller action.
     *
     * @param PackageInterface $package
     * @param null|string $version [optional]
     * @return bool|Response
     */
    public function getDownloadResponse(PackageInterface $package, $version = null)
    {
        if (empty($version)) {
            $version = $package->getVersion();
        }

        $package_file = $this->download($package);
        $fileName = $version . '.zip';

        if (!file_exists($package_file)) {
            return false;
        }

        $fileSize      = filesize($package_file);
        $fileContent   = file_get_contents($package_file);
        $fileMimeType  = $this->getMimeContentType($package_file);

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
     * @param PackageInterface $package
     * @return DownloaderInterface|SvnDownloader
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
                $credentials = $this->svnAuthentication->getCredentialsForUrl($url);
                if ($credentials !== false) {
                    $downloader->setUsername($credentials['username']);
                    $downloader->setPassword($credentials['password']);
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

    /**
     * Get mime-type for file, mime-type will be determined
     * based on the file extension.
     *
     * @param string $filename
     * @return string
     */
    protected function getMimeContentType($filename)
    {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimeType = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimeType;
        } else {
            return 'application/octet-stream';
        }
    }
}
