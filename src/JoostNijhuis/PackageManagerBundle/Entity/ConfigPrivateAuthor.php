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
use JoostNijhuis\PackageManagerBundle\Model\Author;

/**
 * JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateAuthor
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateAuthorRepository")
 * @ORM\Table(name="config_private_authors")
 */
class ConfigPrivateAuthor extends Author
{
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivatePackageVersion", mappedBy="authors")
     */
    protected $packageVersions;
}
