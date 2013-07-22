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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * JoostNijhuis\PackageManagerBundle\Model\PackageVersionInterface
 */
interface PackageVersionInterface
{
    /**
     * Get package version id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set archive array.
     *
     * @param array $archive
     * @return PackageVersionInterface
     */
    public function setArchive(array $archive);

    /**
     * Get archive array.
     *
     * @return array
     */
    public function getArchive();

    /**
     * Set autoload array.
     *
     * @param array $autoLoad
     * @return PackageVersionInterface
     */
    public function setAutoLoad(array $autoLoad);

    /**
     * Get autoload array.
     *
     * @return array
     */
    public function getAutoLoad();

    /**
     * Set bin array.
     *
     * @param array $bin
     * @return PackageVersionInterface
     */
    public function setBin(array $bin);

    /**
     * Get bin array.
     *
     * @return array
     */
    public function getBin();

    /**
     * Set config array.
     *
     * @param array $config
     * @return PackageVersionInterface
     */
    public function setConfig(array $config);

    /**
     * Get config array.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Set conflicts array.
     *
     * @param array $conflicts
     * @return PackageVersionInterface
     */
    public function setConflicts(array $conflicts);

    /**
     * Get conflicts array.
     *
     * @return array
     */
    public function getConflicts();

    /**
     * Set package description.
     *
     * @param string $description
     * @return PackageVersionInterface
     */
    public function setDescription($description);

    /**
     * Get package description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set distribution reference.
     *
     * @param string $distributionReference
     * @return PackageVersionInterface
     */
    public function setDistributionReference($distributionReference);

    /**
     * Get distribution reference.
     *
     * @return string
     */
    public function getDistributionReference();

    /**
     * Set distribution type.
     *
     * @param string $distributionType
     * @return PackageVersionInterface
     */
    public function setDistributionType($distributionType);

    /**
     * Get distribution type.
     *
     * @return string
     */
    public function getDistributionType();

    /**
     * Set distribution url.
     *
     * @param string $distributionUrl
     * @return PackageVersionInterface
     */
    public function setDistributionUrl($distributionUrl);

    /**
     * Get distribution url.
     *
     * @return string
     */
    public function getDistributionUrl();

    /**
     * Set extra array.
     *
     * @param array $extra
     * @return PackageVersionInterface
     */
    public function setExtra(array $extra);

    /**
     * Get extra array.
     *
     * @return array
     */
    public function getExtra();

    /**
     * Set homepage url.
     *
     * @param string $homepage
     * @return PackageVersionInterface
     */
    public function setHomepage($homepage);

    /**
     * Get homepage url.
     *
     * @return string
     */
    public function getHomepage();

    /**
     * Set includes paths array.
     *
     * @param array $includePaths
     * @return PackageVersionInterface
     */
    public function setIncludePaths(array $includePaths);

    /**
     * Get includes paths array.
     *
     * @return array
     */
    public function getIncludePaths();

    /**
     * Set keywords array.
     *
     * @param array $keywords
     * @return PackageVersionInterface
     */
    public function setKeywords(array $keywords);

    /**
     * Get keywords array.
     *
     * @return array
     */
    public function getKeywords();

    /**
     * Set licenses array.
     *
     * @param array $licenses
     * @return PackageVersionInterface
     */
    public function setLicenses(array $licenses);

    /**
     * Get licenses array.
     *
     * @return array
     */
    public function getLicenses();

    /**
     * Set minimum stability.
     *
     * @param string $minimumStability
     * @return PackageVersionInterface
     */
    public function setMinimumStability($minimumStability);

    /**
     * Get minimum stability.
     *
     * @return string
     */
    public function getMinimumStability();

    /**
     * Set prefer stable.
     *
     * @param boolean $preferStable
     * @return PackageVersionInterface
     */
    public function setPreferStable($preferStable);

    /**
     * Get prefer stable.
     *
     * @return boolean
     */
    public function getPreferStable();

    /**
     * Set provides array.
     *
     * @param array $provides
     * @return PackageVersionInterface
     */
    public function setProvides(array $provides);

    /**
     * Get provides array.
     *
     * @return array
     */
    public function getProvides();

    /**
     * Set replaces array.
     *
     * @param array $replaces
     * @return PackageVersionInterface
     */
    public function setReplaces(array $replaces);

    /**
     * Get replaces array.
     *
     * @return array
     */
    public function getReplaces();

    /**
     * Set repositories array.
     *
     * @param array $repositories
     * @return PackageVersionInterface
     */
    public function setRepositories(array $repositories);

    /**
     * Get repositories array.
     *
     * @return array
     */
    public function getRepositories();

    /**
     * Set require development packages array.
     *
     * @param array $requireDevelopmentPackages
     * @return PackageVersionInterface
     */
    public function setRequireDevelopmentPackages(
        array $requireDevelopmentPackages
    );

    /**
     * Get require development packages array.
     *
     * @return array
     */
    public function getRequireDevelopmentPackages();

    /**
     * Set requires array.
     *
     * @param array $requires
     * @return PackageVersionInterface
     */
    public function setRequires(array $requires);

    /**
     * Get requires array.
     *
     * @return array
     */
    public function getRequires();

    /**
     * Set scripts array.
     *
     * @param array $scripts
     * @return PackageVersionInterface
     */
    public function setScripts(array $scripts);

    /**
     * Get scripts array.
     *
     * @return array
     */
    public function getScripts();

    /**
     * Set source reference
     *
     * @param string $sourceReference
     * @return PackageVersionInterface
     */
    public function setSourceReference($sourceReference);

    /**
     * Get source reference.
     *
     * @return string
     */
    public function getSourceReference();

    /**
     * Set source type.
     *
     * @param string $sourceType
     * @return PackageVersionInterface
     */
    public function setSourceType($sourceType);

    /**
     * Get source type.
     *
     * @return string
     */
    public function getSourceType();

    /**
     * Set source url.
     *
     * @param string $sourceUrl
     * @return PackageVersionInterface
     */
    public function setSourceUrl($sourceUrl);

    /**
     * Get source url.
     *
     * @return string
     */
    public function getSourceUrl();

    /**
     * Set suggest array.
     *
     * @param array $suggest
     * @return PackageVersionInterface
     */
    public function setSuggest(array $suggest);

    /**
     * Get suggest array.
     *
     * @return array
     */
    public function getSuggest();

    /**
     * Set support information.
     *
     * @param SupportInterface $support
     * @return PackageVersionInterface
     */
    public function setSupport(SupportInterface $support);

    /**
     * Get support information.
     *
     * @return SupportInterface
     */
    public function getSupport();

    /**
     * Set target directory.
     *
     * @param string $targetDirectory
     * @return PackageVersionInterface
     */
    public function setTargetDirectory($targetDirectory);

    /**
     * Get target directory.
     *
     * @return string
     */
    public function getTargetDirectory();

    /**
     * Get package time.
     *
     * @param \DateTime $time
     * @return PackageVersionInterface
     */
    public function setTime(\DateTime $time);

    /**
     * Set package time.
     *
     * @return \DateTime
     */
    public function getTime();

    /**
     * Set package type.
     *
     * @param string $type
     * @return PackageVersionInterface
     */
    public function setType($type);

    /**
     * Get package type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set unique package id (provided by packagist.org)
     *
     * @param int $uid
     * @return PackageVersionInterface
     */
    public function setUid($uid);

    /**
     * Get unique package id (provided by packagist.org)
     *
     * @return int
     */
    public function getUid();

    /**
     * Set version.
     *
     * @param string $version
     * @return PackageVersionInterface
     */
    public function setVersion($version);

    /**
     * Get version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Set normalized version
     *
     * @param string $versionNormalized
     * @return PackageVersionInterface
     */
    public function setVersionNormalized($versionNormalized);

    /**
     * Get normalized version
     *
     * @return string
     */
    public function getVersionNormalized();

    /**
     * Set package to which this package version belongs.
     *
     * @param PackageInterface $package
     * @return PackageVersionInterface
     */
    public function setPackage(PackageInterface $package);

    /**
     * Get package to which this package version belongs.
     *
     * @return PackageInterface
     */
    public function getPackage();

    /**
     * Set authors attached to this package version.
     *
     * @param ArrayCollection $authors
     * @return PackageVersionInterface
     */
    public function setAuthors(ArrayCollection $authors);

    /**
     * Get authors attached to this package version.
     *
     * @return ArrayCollection
     */
    public function getAuthors();

    /**
     * Remove author from authors list.
     *
     * @param AuthorInterface $author
     * @return PackageVersionInterface
     */
    public function removeAuthor(AuthorInterface $author);

    /**
     * Remove author by name from authors list.
     *
     * @param string $name
     * @return PackageVersionInterface
     */
    public function removeAuthorByName($name);

    /**
     * Check if author is attached to this package version.
     *
     * @param AuthorInterface $author
     * @return bool
     */
    public function hasAuthor(AuthorInterface $author);

    /**
     * Remove all authors from the authors list.
     *
     * @return PackageVersionInterface
     */
    public function removeAuthors();

    /**
     * Add author to the authors list.
     *
     * @param AuthorInterface $author
     * @return PackageVersionInterface
     */
    public function addAuthor(AuthorInterface $author);
}
