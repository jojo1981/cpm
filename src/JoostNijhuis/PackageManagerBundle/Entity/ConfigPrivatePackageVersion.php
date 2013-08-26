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
use JoostNijhuis\PackageManagerBundle\Model\PackageVersion;

/**
 * JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackageVersion
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackageVersionRepository")
 * @ORM\Table(name="config_private_package_versions")
 */
class ConfigPrivatePackageVersion extends PackageVersion
{
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateAuthor", inversedBy="packageVersions")
     * @ORM\JoinTable(name="config_private_package_versions_authors", joinColumns={@ORM\JoinColumn(name="package_version_id", referencedColumnName="id")}, inverseJoinColumns={@ORM\JoinColumn(name="author_id", referencedColumnName="id")})
     */
    protected $authors;

    /**
     * The package this package version belongs to.
     *
     * @var ConfigPrivatePackage
     * @ORM\ManyToOne(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackage", inversedBy="packageVersions")
     */
    protected $package;
}
