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
 * JoostNijhuis\PackageManagerBundle\Model\PackageInterface
 */
interface PackageInterface
{
    /**
     * Get the provider id
     *
     * @return int
     */
    public function getId();

    /**
     * Set the provider name
     *
     * @param string $name
     * @return PackageInterface
     */
    public function setName($name);

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Set provider for this package.
     *
     * @param ProviderInterface $provider
     * @return PackageInterface
     */
    public function setProvider(ProviderInterface $provider);

    /**
     * Get provider of this package.
     *
     * @return ProviderInterface
     */
    public function getProvider();

    /**
     * Set package vendor.
     *
     * @param VendorInterface $vendor
     * @return PackageInterface
     */
    public function setVendor(VendorInterface $vendor);

    /**
     * Get package vendor.
     *
     * @return VendorInterface
     */
    public function getVendor();

    /**
     * Set all package versions attached to this author.
     *
     * @param ArrayCollection $packageVersions
     * @return PackageInterface
     */
    public function setPackageVersions(ArrayCollection $packageVersions);

    /**
     * Get all package versions attached to this author.
     *
     * @return ArrayCollection
     */
    public function getPackageVersions();

    /**
     * Remove all package version from the list of package versions.
     *
     * @param PackageVersionInterface $packageVersion
     * @return PackageInterface
     */
    public function removePackageVersion(PackageVersionInterface $packageVersion);

    /**
     * Remove package version by name from the list of package versions.
     *
     * @param string $version
     * @return PackageInterface
     */
    public function removePackageVersionByVersion($version);

    /**
     * Check if package version is attached to this author.
     *
     * @param PackageVersionInterface $packageVersion
     * @return bool
     */
    public function hasPackageVersion(PackageVersionInterface $packageVersion);

    /**
     * Remove all package version from the list of package versions.
     *
     * @return PackageInterface
     */
    public function removePackageVersions();

    /**
     * Add package version to the list of package versions.
     *
     * @param PackageVersionInterface $packageVersion
     * @return PackageInterface
     */
    public function addPackageVersion(PackageVersionInterface $packageVersion);
}
