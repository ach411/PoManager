<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Revision
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\RevisionRepository")
 */
class Revision
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
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="revision", type="string", length=255, nullable=true)
     */
    private $revision;

    /**
     * @var string
     *
     * @ORM\Column(name="revisionCust", type="string", length=255, nullable=true)
     */
    private $revisionCust;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="drawingPath", type="string", length=255, nullable=true)
     */
    private $drawingPath;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
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
     * Set active
     *
     * @param boolean $active
     * @return Revision
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set revision
     *
     * @param string $revision
     * @return Revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    
        return $this;
    }

    /**
     * Get revision
     *
     * @return string 
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set revisionCust
     *
     * @param string $revisionCust
     * @return Revision
     */
    public function setRevisionCust($revisionCust)
    {
        $this->revisionCust = $revisionCust;
    
        return $this;
    }

    /**
     * Get revisionCust
     *
     * @return string 
     */
    public function getRevisionCust()
    {
        return $this->revisionCust;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Revision
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set drawingPath
     *
     * @param string $drawingPath
     * @return Revision
     */
    public function setDrawingPath($drawingPath)
    {
        $this->drawingPath = $drawingPath;
    
        return $this;
    }

    /**
     * Get drawingPath
     *
     * @return string 
     */
    public function getDrawingPath()
    {
        return $this->drawingPath;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Revision
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

    /**
     * Set product
     *
     * @param \Ach\PoManagerBundle\Entity\Product $product
     * @return Revision
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