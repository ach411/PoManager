<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PartReplacement
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\PartReplacementRepository")
 */
class PartReplacement
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
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Rma", inversedBy="partReplacements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $rma;

    /**
     * @var string
     *
     * @ORM\Column(name="oldPart", type="string", length=255, nullable=true)
     */
    private $oldPart;

    /**
     * @var string
     *
     * @ORM\Column(name="newPart", type="string", length=255, nullable=true)
     */
    private $newPart;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Product")
     * @ORM\JoinColumn(nullable=true)
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;


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
     * Set rma
     *
     * @param \Ach\PoManagerBundle\Entity\Rma $rma
     * @return PartReplacement
     */
    public function setRma(\Ach\PoManagerBundle\Entity\RMA $rma)
    {
        $this->rma = $rma;
    
        return $this;
    }

    /**
     * Get rma
     *
     * @return \Ach\PoManagerBundle\Entity\Rma 
     */
    public function getRma()
    {
        return $this->rma;
    }

    /**
     * Set oldPart
     *
     * @param string $oldPart
     * @return PartReplacement
     */
    public function setOldPart($oldPart)
    {
        $this->oldPart = $oldPart;
    
        return $this;
    }

    /**
     * Get oldPart
     *
     * @return string 
     */
    public function getOldPart()
    {
        return $this->oldPart;
    }

    /**
     * Set newPart
     *
     * @param string $newPart
     * @return PartReplacement
     */
    public function setNewPart($newPart)
    {
        $this->newPart = $newPart;
    
        return $this;
    }

    /**
     * Get newPart
     *
     * @return string 
     */
    public function getNewPart()
    {
        return $this->newPart;
    }

    /**
     * Set product
     *
     * @param \Ach\PoManagerBundle\Entity\Product $product
     * @return PartReplacement
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

    /**
     * Set comment
     *
     * @param string $comment
     * @return PartReplacement
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
}
