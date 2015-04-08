<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PoItem
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\PoItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PoItem
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
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Revision")
     * @ORM\JoinColumn(nullable=false)
     */
    private $revision;

    private $revisionF;

    private $pnF;

    private $custPnF;

    private $priceF;

    private $totalPriceF;

    private $historyF;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Po", inversedBy="poItems")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $po;

    /**
     * @var integer
     *
     * @ORM\Column(name="lineNum", type="integer", nullable=true)
     */
    private $lineNum;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="shippedQty", type="integer")
     */
    private $shippedQty;
	
	/**
     * @var boolean
     *
     * @ORM\Column(name="approved", type="boolean")
     */
    private $approved;
	
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="approvedDate", type="datetime", nullable=true)
     */
    private $approvedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Price")
     * @ORM\JoinColumn(nullable=false)
     */
    private $price;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dueDate", type="date")
     */
    private $dueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="promiseDate", type="date", nullable=true)
     */
    private $promiseDate;
	
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime", nullable=true)
     */
    private $createdDate;
	
	/**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Status")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

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
     * Set lineNum
     *
     * @param integer $lineNum
     * @return PoItem
     */
    public function setLineNum($lineNum)
    {
        $this->lineNum = $lineNum;
    
        return $this;
    }

    /**
     * Get lineNum
     *
     * @return integer 
     */
    public function getLineNum()
    {
        return $this->lineNum;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return PoItem
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     * @return PoItem
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    
        return $this;
    }

    /**
     * Get qty
     *
     * @return integer 
     */
    public function getQty()
    {
        return $this->qty;
    }
	
	/**
     * Set shippedQty
     *
     * @param integer $shippedQty
     * @return PoItem
     */
    public function setShippedQty($shippedQty)
    {
        $this->shippedQty = $shippedQty;
    
        return $this;
    }

    /**
     * Get shippedQty
     *
     * @return integer 
     */
    public function getShippedQty()
    {
        return $this->shippedQty;
    }
	
	/**
     * Set approved
     *
     * @param boolean $approved
     * @return PoItem
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
		if($approved)
		{
			$this->setApprovedDate(new \Datetime());
		}
    
        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean 
     */
    public function getApproved()
    {
        return $this->approved;
    }
	
	/**
	 * Set approvedDate
	 *
	 * @param \DateTime $approvedDate
	 * @return PoItem
	 */
	public function setApprovedDate($approvedDate)
	{
		$this->approvedDate = $approvedDate;
	
		return $this;
	}

	/**
	 * Get approvedDate
	 *
	 * @return \DateTime 
	 */
	public function getApprovedDate()
	{
		return $this->approvedDate;
	}

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return PoItem
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    
        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set promiseDate
     *
     * @param \DateTime $promiseDate
     * @return PoItem
     */
    public function setPromiseDate($promiseDate)
    {
        $this->promiseDate = $promiseDate;
    
        return $this;
    }

    /**
     * Get promiseDate
     *
     * @return \DateTime 
     */
    public function getPromiseDate()
    {
        return $this->promiseDate;
    }
	
	/**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return Po
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    
        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime 
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
    * @ORM\PrePersist
    */
    public function creationDate()
    {
	$this->setCreatedDate(new \Datetime());
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return PoItem
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
     * Set revision
     *
     * @param \Ach\PoManagerBundle\Entity\Revision $revision
     * @return PoItem
     */
    public function setRevision(\Ach\PoManagerBundle\Entity\Revision $revision)
    {
        $this->revision = $revision;
    
        return $this;
    }

    /**
     * Get revision
     *
     * @return \Ach\PoManagerBundle\Entity\Revision 
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set revisionF
     *
     * @param string $revisionF
     * @return PoItem
     */
     public function setRevisionF($revisionF = null)
     {
		
		if(is_null($revisionF))
		{
			$this->revisionF = $this->getRevision()->getRevisionCust();
		}
		else
		{
			$this->revisionF = $revisionF;
		}
		
		return $this;
     }

    /**
     * Get revisionF
     *
     * @return string
     *
     */
     public function getRevisionF()
     {
	return $this->revisionF;
     }

    /**
     * Set pnF
     *
     * @param string $pnF
     * @return PoItem
     */
     public function setPnF($pnF = null)
     {
		 
		if(is_null($pnF))
		{
			$this->pnF = $this->getRevision()->getProduct()->getPn();
		}
		else
		{
			$this->pnF = $pnF;
		}

		return $this;     
     }

    /**
     * Get pnF
     *
     * @return string
     *
     */
     public function getPnF()
     {
	return $this->pnF;
     }

    /**
     * Set custPnF
     *
     * @param string $custPnF
     * @return PoItem
     */
    public function setCustPnF($custPnF = null)
    {
		
		if(is_null($custPnF))
		{
			$this->custPnF = $this->getRevision()->getProduct()->getCustPn();
		}
		else
		{
			$this->custPnF = $custPnF;
		}
		
		return $this;     
    }

    /**
     * Get custPnF
     *
     * @return string
     *
     */
     public function getCustPnF()
     {
	return $this->custPnF;
     }

     /**
     * Set priceF
     *
     * @param string $priceF
     * @return PoItem
     */
     public function setPriceF($priceF = null)
     {
		
		if(is_null($priceF))
		{
			$this->priceF = $this->getPrice()->getPrice();
		}
		else
		{
			$this->priceF = $priceF;
		}
		
		return $this;     
     }

    /**
     * Get priceF
     *
     * @return string
     *
     */
     public function getPriceF()
     {
	return $this->priceF;
     }

     /**
     * Set totalPriceF
     *
     * @param string $totalPriceF
     * @return PoItem
     */
     public function setTotalPriceF($totalPriceF)
     {
	$this->totalPriceF = $totalPriceF;

	return $this;     
     }

    /**
     * Get totalPriceF
     *
     * @return string
     *
     */
     public function getTotalPriceF()
     {
	return $this->totalPriceF;
     }

     /**
     * Set historyF
     *
     * @param string $historyF
     * @return PoItem
     */
     public function setHistoryF($historyF)
     {
	$this->historyF = $historyF;

	return $this;     
     }

    /**
     * Get historyF
     *
     * @return string
     *
     */
     public function getHistoryF()
     {
	return $this->historyF;
     }

    /**
     * Set po
     *
     * @param \Ach\PoManagerBundle\Entity\Po $po
     * @return PoItem
     */
    public function setPo(\Ach\PoManagerBundle\Entity\Po $po)
    {
        $this->po = $po;
    
        return $this;
    }

    /**
     * Get po
     *
     * @return \Ach\PoManagerBundle\Entity\Po 
     */
    public function getPo()
    {
        return $this->po;
    }

    /**
     * Set price
     *
     * @param \Ach\PoManagerBundle\Entity\Price $price
     * @return PoItem
     */
    public function setPrice(\Ach\PoManagerBundle\Entity\Price $price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return \Ach\PoManagerBundle\Entity\Price 
     */
    public function getPrice()
    {
        return $this->price;
    }
	
	/**
     * Set status
     *
     * @param \Ach\PoManagerBundle\Entity\Status $status
     * @return PoItem
     */
    public function setStatus(\Ach\PoManagerBundle\Entity\Status $status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Ach\PoManagerBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

	public function setAllF()
	{
		$this->setPnF();
		$this->setCustPnF();
		$this->setRevisionF();
		$this->setPriceF();
	}
	
    /**
     * Constructor
     */
    public function __construct()
    {
		$this->dueDate = new \Datetime('now');
		$this->shippedQty = 0;
		$this->approved = false;
    }
	
	/**
	 * Reset Item
	 */
	public function resetStatus()
	{
		$this->approved = false;
		$this->getStatus();
	}
}