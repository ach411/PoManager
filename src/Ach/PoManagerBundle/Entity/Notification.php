<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\NotificationRepository")
 */
class Notification
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
	 * @var string
	 *
	 * @ORM\Column(name="notificationSourceClass", type="string", length=255)
	 */
	private $notificationSourceClass;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\NotificationCategory")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $notificationCategory;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\PoItem")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $poItem;

	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Shipment")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $shipment;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Invoice")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $invoice;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Bpo")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $bpo;

	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Rma")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $rma;
    
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
	 * Set notificationSourceClass
	 *
	 * @param string $notificationSourceClass
	 * @return Notification
	 */
	public function setNotificationSourceClass($notificationSourceClass)
	{
		$this->notificationSourceClass = $notificationSourceClass;
		
		return $this;
	}
	
	/**
	 * Get notificationSourceClass
	 *
	 * @return string 
	 */
	public function getNotificationSourceClass()
	{
		return $this->notificationSourceClass;
	}

	/**
	 * Set notificationCategory
	 *
	 * @param \Ach\PoManagerBundle\Entity\NotificationCategory $notificationCategory
	 * @return Notification
	 */
	public function setNotificationCategory(\Ach\PoManagerBundle\Entity\NotificationCategory $notificationCategory)
	{
		$this->notificationCategory = $notificationCategory;
		
		return $this;
	}
	
	/**
	 * Get notificationCategory
	 *
	 * @return \Ach\PoManagerBundle\Entity\NotificationCategory 
	 */
	public function getNotificationCategory()
	{
		return $this->notificationCategory;
	}

	
	/**
	 * Set poItem
	 *
	 * @param \Ach\PoManagerBundle\Entity\PoItem $poItem
	 * @return Notification
	 */
	public function setPoItem(\Ach\PoManagerBundle\Entity\PoItem $poItem)
	{
		$this->poItem = $poItem;
		
		return $this;
	}
	
	/**
	 * Get poItem
	 *
	 * @return \Ach\PoManagerBundle\Entity\PoItem 
	 */
	public function getPoItem()
	{
		return $this->poItem;
	}
	
	/**
	 * Set shipment
	 *
	 * @param \Ach\PoManagerBundle\Entity\Shipment $shipment
	 * @return Notification
	 */
	public function setShipment(\Ach\PoManagerBundle\Entity\Shipment $shipment)
	{
		$this->shipment = $shipment;
		
		return $this;
	}
	
	/**
	 * Get shipment
	 *
	 * @return \Ach\PoManagerBundle\Entity\Shipment 
	 */
	public function getShipment()
	{
		return $this->shipment;
	}
	
	/**
	 * Set invoice
	 *
	 * @param \Ach\PoManagerBundle\Entity\Invoice $invoice
	 * @return Notification
	 */
	public function setInvoice(\Ach\PoManagerBundle\Entity\Invoice $invoice)
	{
		$this->invoice = $invoice;
		
		return $this;
	}
	
	/**
	 * Get invoice
	 *
	 * @return \Ach\PoManagerBundle\Entity\Invoice 
	 */
	public function getInvoice()
	{
		return $this->invoice;
	}
	
	/**
	 * Get bpo
	 *
	 * @return \Ach\PoManagerBundle\Entity\Bpo 
	 */
	public function getBpo()
	{
		return $this->bpo;
	}
	
	/**
	 * Set bpo
	 *
	 * @param \Ach\PoManagerBundle\Entity\Bpo $bpo
	 * @return Notification
	 */
	public function setBpo(\Ach\PoManagerBundle\Entity\Bpo $bpo)
	{
		$this->bpo = $bpo;
		
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
	 * Set rma
	 *
	 * @param \Ach\PoManagerBundle\Entity\Rma $rma
	 * @return Notification
	 */
	public function setRma(\Ach\PoManagerBundle\Entity\Rma $rma)
	{
		$this->rma = $rma;
		
		return $this;
	}
	
	
	public function __construct($notificationSource, $notificationCategory)
	{
		// get the class of notificationSource
		preg_match("/\\\\([\w]+)$/", get_class($notificationSource), $output_array);
		$this->notificationSourceClass = $output_array[1];
		
		// set the right attribute
		//$setter = "set".$output_array[1];
		//$this->$setter($notificationSource);
		
		/*
		switch($this->notificationSourceClass)
		{
			case "PoItem":
				$this->poItem = $notificationSource;
				break;
			case "Shipment":
				$this->shipment = $notificationSource;
				break;
			case "Invoice":
				$this->invoice = $notificationSource;
				break;
			case "Bpo":
				$this->bpo = $notificationSource;
				break;
			default:
				echo "Error: notificationSource can't be identified";
		}
		*/
		$notificationSourceAttribute = lcfirst($this->notificationSourceClass);
		$this->$notificationSourceAttribute = $notificationSource;
		
		$this->notificationCategory = $notificationCategory;
		
		//echo 'Notification created with Source Class: ' . $this->notificationSourceClass . ' and Category: ' . $notificationCategory->getName();
		//echo (is_null($this->invoice)? 'invoice null' : 'invoice not null');
		//echo (is_null($this->poItem)? 'poItem null' : 'poItem not null');
		
	}

    // get the ID of the RMA source (for debug purpose)
    public function getSourceId()
    {
        if(isset($this->poItem)) {
            return $this->getPoItem()->getId();
        }

        if(isset($this->shipment)) {
            return $this->getShipment()->getId();
        }

        if(isset($this->invoice)) {
            return $this->getInvoice()->getId();
        }

        if(isset($this->bpo)) {
            return $this->getBpo()->getId();
        }

        if(isset($this->rma)) {
            return $this->getRma()->getId();
        }

    }
	
}