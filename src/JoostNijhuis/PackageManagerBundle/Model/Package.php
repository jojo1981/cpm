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
 * JoostNijhuis\PackageManagerBundle\Model\Package
 */
abstract class Package implements PackageInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var VendorInterface
     */
    protected $vendor;

    /**
     * @var ArrayCollection
     */
    protected $packageVersions;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * {@inheritDoc}
     */
    public function setVendor(VendorInterface $vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * {@inheritDoc}
     */
    public function setPackageVersions(ArrayCollection $packageVersions)
    {
        $this->packageVersions = $packageVersions;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageVersions()
    {
        return $this->packageVersions;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackageVersion(PackageVersionInterface $packageVersion)
    {
        $this->packageVersions->removeElement($packageVersion);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackageVersionByVersion($version)
    {
        /** @var PackageVersionInterface $packageVersion$package */
        foreach ($this->packageVersions as $packageVersion) {
            if ($packageVersion->getVersion() == $version) {
                $this->packageVersions->removeElement($packageVersion);
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPackageVersion(PackageVersionInterface $packageVersion)
    {
        /** @var PackageVersionInterface $objPackageVersion */
        foreach ($this->packageVersions as $objPackageVersion) {
            if ($objPackageVersion->getId() == $packageVersion->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackageVersions()
    {
        $this->packageVersions->clear();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPackageVersion(PackageVersionInterface $packageVersion)
    {
        $this->packageVersions->add($packageVersion);

        return $this;
    }
}
