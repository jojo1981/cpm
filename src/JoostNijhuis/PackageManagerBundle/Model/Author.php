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
 * JoostNijhuis\PackageManagerBundle\Model\Author
 */
abstract class Author implements AuthorInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Full name of the author.
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * Email address of the author.
     *
     * @var null|string
     * @ORM\Column(name="email_address", type="string", length=255, nullable=true)
     */
    protected $emailAddress;

    /**
     * Homepage URL for the author.
     *
     * @var null|string
     * @ORM\Column(name="homepage", type="string", length=255, nullable=true)
     */
    protected $homepage;

    /**
     * Author's role in the project.
     *
     * @var null|string
     * @ORM\Column(name="role", type="string", length=255, nullable=true)
     */
    protected $role;

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
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * {@inheritDoc}
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    public function setPackageVersions($packageVersions)
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
    public function removePackageVersionByName($name)
    {
        /** @var Package $package */
        foreach ($this->packageVersions as $packageVersion) {
            if ($packageVersion->getName() == $name) {
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
        $this->packages->add($packageVersion);

        return $this;
    }
}
