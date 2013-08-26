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
use JoostNijhuis\PackageManagerBundle\Model\Vendor;

/**
 * JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateVendor
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateVendorRepository")
 * @ORM\Table(name="config_private_vendors")
 */
class ConfigPrivateVendor extends Vendor
{
    /**
     * Packages attached to this vendor.
     *
     * @var ArrayCollection
     * @ORM\OneToOne(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackage", mappedBy="vendor")
     */
    protected $packages;
}
