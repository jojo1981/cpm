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
 * JoostNijhuis\PackageManagerBundle\Model\AuthorInterface
 */
interface AuthorInterface
{
    /**
     * Get author id
     *
     * @return int
     */
    public function getId();

    /**
     * Set author name
     *
     * @param string $name
     * @return AuthorInterface
     */
    public function setName($name);

    /**
     * Get author name
     *
     * @return string
     */
    public function getName();

    /**
     * Set author email address
     *
     * @param null|string $emailAddress
     * @return AuthorInterface
     */
    public function setEmailAddress($emailAddress);

    /**
     * Get author email address
     *
     * @return null|string
     */
    public function getEmailAddress();

    /**
     * Get author homepage
     *
     * @param null|string $homepage
     * @return AuthorInterface
     */
    public function setHomepage($homepage);

    /**
     * Get author homepage
     *
     * @return null|string
     */
    public function getHomepage();

    /**
     * Get author role
     *
     * @param null|string $role
     * @return AuthorInterface
     */
    public function setRole($role);

    /**
     * Get author role
     *
     * @return null|string
     */
    public function getRole();

    /**
     * Set all package versions attached to this author.
     *
     * @param ArrayCollection $packageVersions
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function removePackageVersion(PackageVersionInterface $packageVersion);

    /**
     * Remove package version by name from the list of package versions.
     *
     * @param string $version
     * @return AuthorInterface
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
     * @return AuthorInterface
     */
    public function removePackageVersions();

    /**
     * Add package version to the list of package versions.
     *
     * @param PackageVersionInterface $packageVersion
     * @return AuthorInterface
     */
    public function addPackageVersion(PackageVersionInterface $packageVersion);
}
