<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AlertBpo
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\AlertBpoRepository")
 */
class AlertBpo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Ach\PoManagerBundle\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var integer
     *
     * @ORM\Column(name="thresholdQty", type="integer")
     */
    private $thresholdQty;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;


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
     * Set thresholdQty
     *
     * @param integer $thresholdQty
     * @return AlertBpo
     */
    public function setThresholdQty($thresholdQty)
    {
        $this->thresholdQty = $thresholdQty;
    
        return $this;
    }

    /**
     * Get thresholdQty
     *
     * @return integer 
     */
    public function getThresholdQty()
    {
        return $this->thresholdQty;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return AlertBpo
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set product
     *
     * @param \Ach\PoManagerBundle\Entity\Product $product
     * @return AlertBpo
     */
    public function setProduct(\Ach\PoManagerBundle\Entity\Product $product)
    {
        $this->product = $product;
    
        return $this;
    }

    /**
     * Get product
     *
     * @return \Ach\PoManagerBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }
}