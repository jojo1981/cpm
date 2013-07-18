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
 * JoostNijhuis\PackageManagerBundle\Entity\Language
 *
 * @ORM\Entity(repositoryClass="JoostNijhuis\PackageManagerBundle\Entity\LanguageRepository")
 * @ORM\Table(name="languages")
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;
    
    /**
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    protected $code;
    
    /**
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = 0;
    
    /**
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    protected $sortOrder;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Language
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code
     *
     * @param integer $code
     * @return Language
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return Language
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return integer 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return Language
     */
    public function setOrder($order)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set sort order
     *
     * @param integer $sortOrder
     * @return Language
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    
        return $this;
    }

    /**
     * Get sort order
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}