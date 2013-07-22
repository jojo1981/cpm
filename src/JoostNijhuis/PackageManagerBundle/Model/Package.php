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

/**
 * JoostNijhuis\PackageManagerBundle\Model\Package
 */
abstract class Package implements PackageInterface
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
     * @var string
     *
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
}
