<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Builder\Config;

use Symfony\Component\Filesystem\Filesystem;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\Config\Config
 */
class Config
{
    /**
     * @var null|string
     */
    protected $downloadUrlPrefix;

    /**
     * @var bool
     */
    protected $parse = true;

    /**
     * @var string
     */
    protected $notify = '/notify/%package%';

    /**
     * @var string
     */
    protected $notifyBatch = '/notify/';

    /**
     * @var bool
     */
    protected $attachPrivatePackages = false;

    /**
     * @var string
     */
    protected $privatePackagesConfigFile = '';

    /**
     * @var string
     */
    protected $privatePackagesFile = '';

    /**
     * @var string
     */
    protected $packageDataDirectory;

    /**
     * @var int
     */
    protected $uid = 99999999;

    /**
     * @var string
     */
    protected $baseUrl = 'http://packagist.org';

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var string
     */
    protected $indexPath;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var bool
     */
    protected $enablePackagistProxy;

    /**
     * Constructor
     */
    public function __construct(array $config = array(), $cacheDir)
    {
        $this->fs = new Filesystem();
        $this->cacheDir = $cacheDir;
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'enable_packagist_proxy':
                    $this->setEnablePackagistProxy($value);
                    break;
                case 'parse_packages':
                    $this->setParse($value);
                    break;
                case 'attach_private_packages':
                    $this->setAttachPrivatePackages($value);
                    break;
                case 'packagist_url':
                    $this->setBaseUrl($value);
                    break;
                case 'private_packages_config_file':
                    $this->setPrivatePackagesConfigFile($value);
                    break;
                case 'private_packages_output_file':
                    $this->setPrivatePackagesFile($value);
                    break;
                case 'private_packages_data_dir':
                    $this->setPackageDataDirectory($value);
                    break;
                case 'packages_index_dir':
                    $this->setIndexPath($value);
                    break;
                case 'tmp_dir':
                    $this->setTmpPath($value);
                    break;
            }
        }
    }

    /**
     * Set the download url prefix, this will be used when parse
     * is enabled by the method
     *
     * @param string $downloadUrlPrefix
     */
    public function setDownloadUrlPrefix($downloadUrlPrefix)
    {
        $this->downloadUrlPrefix = $downloadUrlPrefix;
    }

    /**
     * Set if the packages needs to be parse. When setting this to
     * false the original package data will be returned by this
     * Composer Repository.
     *
     * @param bool $parse
     */
    public function setParse($parse)
    {
        $this->parse = $parse;
    }

    /**
     * @param string $notify
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;
    }

    /**
     * @param string $notifyBatch
     */
    public function setNotifyBatch($notifyBatch)
    {
        $this->notifyBatch = $notifyBatch;
    }

    /**
     * If packages need to be parsed this is the url
     * to prefix this packages source and dist.
     *
     * @return string
     */
    public function getDownloadUrlPrefix()
    {
        if (!empty($this->downloadUrlPrefix)) {
            return $this->downloadUrlPrefix;
        }

        $schema = 'http://';
        if (isset($_SERVER['HTTPS'])) {
            $schema = 'https://';
        }

        return $schema . $_SERVER['HTTP_HOST'] . '/downloads/';
    }

    /**
     * Return if packages needs to be parsed
     *
     * @return boolean
     */
    public function getParse()
    {
        return $this->parse;
    }

    /**
     * @return string
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * @return string
     */
    public function getNotifyBatch()
    {
        return $this->notifyBatch;
    }

    /**
     * @param boolean $attachPrivatePackages
     */
    public function setAttachPrivatePackages($attachPrivatePackages)
    {
        $this->attachPrivatePackages = $attachPrivatePackages;
    }

    /**
     * @return boolean
     */
    public function getAttachPrivatePackages()
    {
        return $this->attachPrivatePackages;
    }

    /**
     * @param string $privatePackagesFile
     */
    public function setPrivatePackagesFile($privatePackagesFile)
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($privatePackagesFile) === false) {
            $fileSystem->touch($privatePackagesFile);
        }

        $privatePackagesFile = realpath($privatePackagesFile);
        $this->privatePackagesFile = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $privatePackagesFile
        );
    }

    /**
     * @return string
     */
    public function getPrivatePackagesFile()
    {
        return $this->privatePackagesFile;
    }

    /**
     * Increase uid with 1
     */
    public function incUid()
    {
        $this->uid++;
    }

    /**
     * get current uid and increase with 1
     *
     * @return int
     */
    public function getUid()
    {
        $retValue = $this->uid;
        $this->incUid();

        return $retValue;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $indexPath
     */
    public function setIndexPath($indexPath)
    {
        $this->indexPath = str_replace('/', DIRECTORY_SEPARATOR, $indexPath);
    }

    /**
     * @return string
     */
    public function getIndexPath()
    {
        return $this->indexPath;
    }

    /**
     * @param string $tmpPath
     */
    public function setTmpPath($tmpPath)
    {
        $this->fs->mkdir($tmpPath, 0777, true);
        $tmpPath = realpath($tmpPath);
        $this->tmpPath = str_replace('/', DIRECTORY_SEPARATOR, $tmpPath);
    }

    /**
     * @return string
     */
    public function getTmpPath()
    {
        return $this->tmpPath;
    }

    /**
     * @param string $privatePackagesConfigFile
     */
    public function setPrivatePackagesConfigFile($privatePackagesConfigFile)
    {
        $this->privatePackagesConfigFile = $privatePackagesConfigFile;
    }

    /**
     * @return string
     */
    public function getPrivatePackagesConfigFile()
    {
        return $this->privatePackagesConfigFile;
    }

    /**
     * @param string $packageDataDirectory
     */
    public function setPackageDataDirectory($packageDataDirectory)
    {
        $this->fs->mkdir($packageDataDirectory, 0777, true);
        $packageDataDirectory = realpath($packageDataDirectory);
        $this->packageDataDirectory = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $packageDataDirectory
        );
    }

    /**
     * @return string
     */
    public function getPackageDataDirectory()
    {
        return $this->packageDataDirectory;
    }

    /**
     * @param boolean $enablePackagistProxy
     */
    public function setEnablePackagistProxy($enablePackagistProxy)
    {
        $this->enablePackagistProxy = $enablePackagistProxy;
    }

    /**
     * @return boolean
     */
    public function getEnablePackagistProxy()
    {
        return $this->enablePackagistProxy;
    }

    /**
     * Get Composer config
     *
     * @return array
     */
    public function getComposerConfig()
    {
        $cacheDir = $this->cacheDir . '/composer';
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

    /**
     * Magic method for converting this object to a string
     *
     * @return string
     */
    public function __toString()
    {
        $retValue = '';

        $retValue .= 'Attach private packages: ' . $this->getAttachPrivatePackages() . "\n";
        $retValue .= 'Base Url: ' . $this->getBaseUrl() . "\n";
        $retValue .= 'Index directory: ' . $this->getIndexPath() . "\n";
        $retValue .= 'Tmp directory: ' . $this->getTmpPath() . "\n";
        $retValue .= 'Packages data directory: ' . $this->getPackageDataDirectory() . "\n";
        $retValue .= 'Download URL prefix: ' . $this->getDownloadUrlPrefix() . "\n";
        $retValue .= 'Notify: ' . $this->getNotify() . "\n";
        $retValue .= 'Notify batch: ' . $this->getNotifyBatch() . "\n";
        $retValue .= 'Parse packages: ' . $this->getParse() . "\n";
        $retValue .= 'Packagist proxy enabled: ' . $this->getEnablePackagistProxy() . "\n";
        $retValue .= 'Private packages config file: ' . $this->getPrivatePackagesConfigFile() . "\n";
        $retValue .= 'Private packages output file: ' . $this->getPrivatePackagesFile() . "\n";
        $retValue .= 'Start uid index: ' . $this->getUid() . "\n";

        return $retValue;
    }
}
