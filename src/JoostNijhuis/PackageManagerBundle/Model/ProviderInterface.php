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
 * JoostNijhuis\PackageManagerBundle\Model\ProviderInterface
 */
interface ProviderInterface
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
     * @return ProviderInterface
     */
    public function setName($name);

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Set packages for this provider.
     *
     * @param ArrayCollection $packages
     * @return ProviderInterface
     */
    public function setPackages($packages);

    /**
     * Get packages of this provider.
     *
     * @return ArrayCollection
     */
    public function getPackages();

    /**
     * Remove a package belonging to this provider by package
     *
     * @param PackageInterface $package
     * @return ProviderInterface
     */
    public function removePackage(PackageInterface $package);

    /**
     * Remove a package belonging to this provider by name
     *
     * @param string $name
     * @return ProviderInterface
     */
    public function removePackageByName($name);

    /**
     * Check if package is attached to this vendor
     *
     * @param PackageInterface $package
     * @return bool
     */
    public function hasPackage(PackageInterface $package);

    /**
     * Remove all packages from this provider.
     * @return ProviderInterface
     */
    public function removePackages();

    /**
     * Add a package to this vendor
     *
     * @param PackageInterface $package
     * @return ProviderInterface
     */
    public function addPackage(PackageInterface $package);
}
