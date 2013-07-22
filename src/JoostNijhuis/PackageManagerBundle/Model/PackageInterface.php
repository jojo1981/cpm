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
}
