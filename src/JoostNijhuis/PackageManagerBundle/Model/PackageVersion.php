<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * JoostNijhuis\PackageManagerBundle\Model\PackageVersion
 */
abstract class PackageVersion implements PackageVersionInterface
{
    /**
     * Package id.
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Package type, either 'library' for common packages, 'composer-installer'
     * for custom  installers, 'meta-package' for  empty packages, or  a custom
     * type ([a-z0-9-]+) defined by whatever project this package applies to.
     *
     * @var string
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type;

    /**
     * Forces the  package to  be installed into  the given  subdirectory path.
     * This is  used for autoloading PSR-0  packages that do not  contain their
     * full path. Use forward slashes for cross-platform compatibility.
     *
     * @var string
     * @ORM\Column(name="target_directory", type="string", length=255, nullable=true)
     */
    protected $targetDirectory;

    /**
     * Short package description.
     *
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * A tag/keyword that this package relates to.
     *
     * @var array
     * @ORM\Column(name="keywords", type="array", nullable=true)
     */
    protected $keywords;

    /**
     * Homepage URL for the project.
     *
     * @var string
     * @ORM\Column(name="homepage", type="string", length=255, nullable=true)
     */
    protected $homepage;

    /**
     * Package version, see http://getcomposer.org/doc/04-schema.md#version for
     * more info on valid schemes.
     *
     * @var string
     * @ORM\Column(name="version", type="string", length=255, nullable=false)
     */
    protected $version;

    /**
     * Package normalized version.
     *
     * @var string
     * @ORM\Column(name="version_normalized", type="string", length=255, nullable=false)
     */
    protected $versionNormalized;

    /**
     * Package release date, in 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' format.
     *
     * @var \DateTime
     * @ORM\Column(name="time", type="datetime", nullable=true)
     */
    protected $time;

    /**
     * Array of license names.
     *
     * @var array
     * @ORM\Column(name="licenses", type="array", nullable=true)
     */
    protected $licenses;

    /**
     * This is a  hash of package name (keys) and  version constraints (values)
     * that are required to run this package.
     *
     * @var array
     * @ORM\Column(name="requires", type="array", nullable=true)
     */
    protected $requires;

    /**
     * This is a  hash of package name (keys) and  version constraints (values)
     * that can be replaced by this package.
     *
     * @var array
     * @ORM\Column(name="replaces", type="array", nullable=true)
     */
    protected $replaces;

    /**
     * This is a  hash of package name (keys) and  version constraints (values)
     * that conflict with this package.
     *
     * @var array
     * @ORM\Column(name="conflicts", type="array", nullable=true)
     */
    protected $conflicts;

    /**
     * This is a  hash of package name (keys) and  version constraints (values)
     * that this package provides in addition to this package's name.
     *
     * @var array
     * @ORM\Column(name="provides", type="array", nullable=true)
     */
    protected $provides;

    /**
     * This is a  hash of package name (keys) and  version constraints (values)
     * that this package requires for developing it (testing tools and such).
     *
     * @var array
     * @ORM\Column(name="require_dev_packages", type="array", nullable=true)
     */
    protected $requireDevelopmentPackages;

    /**
     * This is  a hash of  package name  (keys) and descriptions  (values) that
     * this package suggests  work well with it (this will  be suggested to the
     * user during installation).
     *
     * @var array
     * @ORM\Column(name="suggest", type="array", nullable=true)
     */
    protected $suggest;

    /**
     * Composer options.
     *
     * @var array
     * @ORM\Column(name="config", type="array", nullable=true)
     */
    protected $config;

    /**
     * Arbitrary extra data that can be used by custom installers, for example,
     * package of type composer-installer must  have a 'class' key defining the
     * installer class name.
     *
     * @var array
     * @ORM\Column(name="extra", type="array", nullable=true)
     */
    protected $extra;

    /**
     * Description of how the package can be auto-loaded.
     *
     * @var array
     * @ORM\Column(name="autoload", type="array", nullable=true)
     */
    protected $autoLoad;

    /**
     * Options for creating package archives for distribution.
     *
     * @var array
     * @ORM\Column(name="archive", type="array", nullable=true)
     */
    protected $archive;

    /**
     * A set of additional repositories where packages can be found.
     *
     * @var array
     * @ORM\Column(name="repositories", type="array", nullable=true)
     */
    protected $repositories;

    /**
     * The  minimum  stability  the  packages must  have  to  be  install-able.
     * Possible values are: dev, alpha, beta, RC, stable.
     *
     * @var string
     * @ORM\Column(name="minimum_stability", type="string", length=255, nullable=true)
     */
    protected $minimumStability;

    /**
     * If set to  true, stable packages will be preferred  to dev packages when
     * possible, even if the minimum-stability allows unstable packages.
     *
     * @var bool
     * @ORM\Column(name="prefer_stable", type="boolean", nullable=false)
     */
    protected $preferStable = false;

    /**
     * A set  of files that  should be treated  as binaries and  symlinked into
     * bin-dir (from config).
     *
     * @var array
     * @ORM\Column(name="bin", type="array", nullable=true)
     */
    protected $bin;

    /**
     * A list of directories which should get added to PHP's include path. This
     * is only  present to  support legacy  projects, and  all new  code should
     * preferably use autoloading.
     *
     * @var array
     * @ORM\Column(name="include_paths", type="array", nullable=true)
     */
    protected $includePaths;

    /**
     * Scripts listeners that will be executed before/after some events.
     *
     * @var array
     * @ORM\Column(name="scripts", type="array", nullable=true)
     */
    protected $scripts;

    /**
     * Scripts listeners that will be executed before/after some events.
     *
     * @var SupportInterface
     */
    protected $support;

    /**
     * Source type.
     *
     * @var string
     * @ORM\Column(name="source_type", type="string", length=255, nullable=true)
     */
    protected $sourceType;

    /**
     * Source url.
     *
     * @var string
     * @ORM\Column(name="source_url", type="string", length=255, nullable=true)
     */
    protected $sourceUrl;

    /**
     * Source reference.
     *
     * @var string
     * @ORM\Column(name="source_reference", type="string", length=255, nullable=true)
     */
    protected $sourceReference;

    /**
     * Distribution type.
     *
     * @var string
     * @ORM\Column(name="distribution_type", type="string", length=255, nullable=true)
     */
    protected $distributionType;

    /**
     * Distribution url.
     *
     * @var string
     * @ORM\Column(name="distribution_url", type="string", length=255, nullable=true)
     */
    protected $distributionUrl;

    /**
     * Distribution reference.
     *
     * @var string
     * @ORM\Column(name="distribution_reference", type="string", length=255, nullable=true)
     */
    protected $distributionReference;

    /**
     * Unique package version identifier
     *
     * @var int
     * @ORM\Column(name="uid", type="integer", nullable=true)
     */
    protected $uid;

    /**
     * List of authors  that contributed to the package. This  is typically the
     * main maintainers, not the full list.
     *
     * @var ArrayCollection
     */
    protected $authors;

    /**
     * The package this package version belongs to.
     *
     * @var PackageInterface
     */
    protected $package;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set archive array.
     *
     * @param array $archive
     * @return PackageVersion
     */
    public function setArchive(array $archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * {@inheritDoc}
     */
    public function setAutoLoad(array $autoLoad)
    {
        $this->autoLoad = $autoLoad;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoLoad()
    {
        return $this->autoLoad;
    }

    /**
     * {@inheritDoc}
     */
    public function setBin(array $bin)
    {
        $this->bin = $bin;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function setConflicts(array $conflicts)
    {
        $this->conflicts = $conflicts;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConflicts()
    {
        return $this->conflicts;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function setDistributionReference($distributionReference)
    {
        $this->distributionReference = $distributionReference;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistributionReference()
    {
        return $this->distributionReference;
    }

    /**
     * {@inheritDoc}
     */
    public function setDistributionType($distributionType)
    {
        $this->distributionType = $distributionType;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistributionType()
    {
        return $this->distributionType;
    }

    /**
     * {@inheritDoc}
     */
    public function setDistributionUrl($distributionUrl)
    {
        $this->distributionUrl = $distributionUrl;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistributionUrl()
    {
        return $this->distributionUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * {@inheritDoc}
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * {@inheritDoc}
     */
    public function setIncludePaths(array $includePaths)
    {
        $this->includePaths = $includePaths;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * {@inheritDoc}
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * {@inheritDoc}
     */
    public function setLicenses(array $licenses)
    {
        $this->licenses = $licenses;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * {@inheritDoc}
     */
    public function setMinimumStability($minimumStability)
    {
        $this->minimumStability = $minimumStability;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMinimumStability()
    {
        return $this->minimumStability;
    }

    /**
     * {@inheritDoc}
     */
    public function setPreferStable($preferStable)
    {
        $this->preferStable = $preferStable;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPreferStable()
    {
        return $this->preferStable;
    }

    /**
     * {@inheritDoc}
     */
    public function setProvides(array $provides)
    {
        $this->provides = $provides;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * {@inheritDoc}
     */
    public function setReplaces(array $replaces)
    {
        $this->replaces = $replaces;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * {@inheritDoc}
     */
    public function setRepositories(array $repositories)
    {
        $this->repositories = $repositories;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequireDevelopmentPackages(
        array $requireDevelopmentPackages
    ) {
        $this->requireDevelopmentPackages = $requireDevelopmentPackages;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequireDevelopmentPackages()
    {
        return $this->requireDevelopmentPackages;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequires(array $requires)
    {
        $this->requires = $requires;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * {@inheritDoc}
     */
    public function setScripts(array $scripts)
    {
        $this->scripts = $scripts;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * {@inheritDoc}
     */
    public function setSourceReference($sourceReference)
    {
        $this->sourceReference = $sourceReference;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceReference()
    {
        return $this->sourceReference;
    }

    /**
     * {@inheritDoc}
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * {@inheritDoc}
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setSuggest(array $suggest)
    {
        $this->suggest = $suggest;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggest()
    {
        return $this->suggest;
    }

    /**
     * {@inheritDoc}
     */
    public function setSupport(SupportInterface $support)
    {
        $this->support = $support;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * {@inheritDoc}
     */
    public function setTargetDirectory($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * {@inheritDoc}
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * {@inheritDoc}
     */
    public function setVersion($version)
    {
        $this->version = $version;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritDoc}
     */
    public function setVersionNormalized($versionNormalized)
    {
        $this->versionNormalized = $versionNormalized;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersionNormalized()
    {
        return $this->versionNormalized;
    }

    /**
     * {@inheritDoc}
     */
    public function setPackage(PackageInterface $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthors(ArrayCollection $authors)
    {
        $this->authors = $authors;

        return $this;
    }

    /**
     * Get authors attached to this package version.
     *
     * @return ArrayCollection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAuthor(AuthorInterface $author)
    {
        $this->authors->removeElement($author);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAuthorByName($name)
    {
        /** @var AuthorInterface $author */
        foreach ($this->authors as $author) {
            if ($author->getName() == $name) {
                $this->authors->removeElement($author);
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthor(AuthorInterface $author)
    {
        /** @var AuthorInterface $objAuthor */
        foreach ($this->authors as $objAuthor) {
            if ($objAuthor->getId() == $author->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAuthors()
    {
        $this->authors->clear();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addAuthor(AuthorInterface $author)
    {
        $this->authors->add($author);

        return $this;
    }
}
