<?php

namespace JoostNijhuis\PackageManagerBundle\Builder;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\ParseConfig
 */
class ParseConfig
{

    /**
     * @var string
     */
    protected $downloadUrlPrefix;

    /**
     * @var bool
     */
    protected $parseOnlyStable;

    /**
     * @var bool
     */
    protected $parse;

    /**
     * @var string
     */
    protected $notify;

    /**
     * @var string
     */
    protected $notifyBatch;

    /**
     * @var bool
     */
    protected $attachPrivatePackages = false;

    /**
     * @var string
     */
    protected $privatePackagesFile = '';

    /**
     * @var string
     */
    protected $attachTo = 'p/provider-active$%hash%.json';

    /**
     * @var string
     */
    protected $privatePackagesProviderName = 'sqills/packages';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->downloadUrlPrefix = 'http://packages.sqills.com/downloads/';
        $this->parseOnlyStable = true;
        $this->parse = true;
        $this->notify = '/notify/%package%';
        $this->notifyBatch = '/notify/';
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
     * Set if only stable package versions needs to be parse.
     * When parsing a package the dist and source will be replace
     * with an url prefix a configured by the setDownloadUrlPrefix
     * method inside this class. The purpose of this is to trigger
     * a download when this package needs to be retrieved. We will
     * save a copy of that package as a backup.
     *
     * @param bool $parseOnlyStable
     */
    public function setParseOnlyStable($parseOnlyStable)
    {
        $this->parseOnlyStable = $parseOnlyStable;
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
        return $this->downloadUrlPrefix;
    }

    /**
     * Returns if only stable packages needs to be parsed
     *
     * @return boolean
     */
    public function getParseOnlyStable()
    {
        return $this->parseOnlyStable;
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
     * @param string $attachTo
     */
    public function setAttachTo($attachTo)
    {
        $this->attachTo = $attachTo;
    }

    /**
     * @return string
     */
    public function getAttachTo()
    {
        return $this->attachTo;
    }

    /**
     * @param string $privatePackagesFile
     */
    public function setPrivatePackagesFile($privatePackagesFile)
    {
        $this->privatePackagesFile = $privatePackagesFile;
    }

    /**
     * @return string
     */
    public function getPrivatePackagesFile()
    {
        return $this->privatePackagesFile;
    }

    /**
     * @param string $privatePackagesProviderName
     */
    public function setPrivatePackagesProviderName($privatePackagesProviderName)
    {
        $this->privatePackagesProviderName = $privatePackagesProviderName;
    }

    /**
     * @return string
     */
    public function getPrivatePackagesProviderName()
    {
        return $this->privatePackagesProviderName;
    }

}
