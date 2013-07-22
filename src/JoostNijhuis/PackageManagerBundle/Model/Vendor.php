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
 * JoostNijhuis\PackageManagerBundle\Model\Vendor
 */
abstract class Vendor implements VendorInterface
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
     * Packages attached to this vendor.
     *
     * @var ArrayCollection
     */
    protected $packages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->packages = new ArrayCollection();
    }

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
    public function setPackages($packages)
    {
        $this->packages = $packages;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackage(PackageInterface $package)
    {
        $this->packages->removeElement($package);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackageByName($name)
    {
        /** @var PackageInterface $package */
        foreach ($this->packages as $package) {
            if ($package->getName() == $name) {
                $this->packages->removeElement($package);
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPackage(PackageInterface $package)
    {
        /** @var PackageInterface $objPackage */
        foreach ($this->packages as $objPackage) {
            if ($objPackage->getId() == $package->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removePackages()
    {
        $this->packages->clear();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addPackage(PackageInterface $package)
    {
        $this->packages->add($package);

        return $this;
    }
}
