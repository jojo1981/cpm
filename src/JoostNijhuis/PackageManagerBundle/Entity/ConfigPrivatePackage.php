<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JoostNijhuis\PackageManagerBundle\Model\Package;

/**
 * JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackage
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackageRepository")
 * @ORM\Table(name="config_private_packages")
 */
class ConfigPrivatePackage extends Package
{
    /**
     * @var ConfigPrivateVendor
     * @ORM\OneToOne(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateVendor", inversedBy="packages")
     */
    protected $vendor;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackageVersion", mappedBy="package")
     */
    protected $packageVersions;
}
