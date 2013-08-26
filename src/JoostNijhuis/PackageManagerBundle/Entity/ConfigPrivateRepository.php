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

/**
 * JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateRepository
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\ConfigPrivateRepositoryRepository")
 * @ORM\Table(name="config_private_repositories")
 */
class ConfigPrivateRepository
{
    const REPOSITORY_TYPE_COMPOSER = 'composer';
    const REPOSITORY_TYPE_VCS      = 'vcs';
    const REPOSITORY_TYPE_PACKAGE  = 'package';
    const REPOSITORY_TYPE_PEAR     = 'pear';
    const REPOSITORY_TYPE_GIT      = 'git';
    const REPOSITORY_TYPE_SVN      = 'svn';
    const REPOSITORY_TYPE_HG       = 'hg';
    const REPOSITORY_TYPE_ARTIFACT = 'artifact';

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
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * Get the provider id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the private package repository type
     *
     * @param string $type
     * @return ConfigPrivateRepository
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the private package repository type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the private package repository url
     *
     * @param string $url
     * @return ConfigPrivateRepository
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the private package repository url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
