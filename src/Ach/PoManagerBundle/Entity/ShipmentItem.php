<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipmentItem
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\ShipmentItemRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ShipmentItem
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
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Shipment", inversedBy="shipmentItems")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $shipment;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\PoItem")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $poItem;

	/**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Invoice", inversedBy="shipmentItems")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $invoice;
	
    /**
     * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\ShipmentBatch", mappedBy="shipmentItem")
     */
    private $shipmentBatches;
    
	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdDate", type="datetime", nullable=true)
	 */
	private $createdDate;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="qty", type="integer", nullable=false)
	 */
	private $qty;
	
	
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
	 * Set shipment
	 *
	 * @param \Ach\PoManagerBundle\Entity\Shipment $shipment
	 * @return ShipmentItem
	 */
	public function setShipment(\Ach\PoManagerBundle\Entity\Shipment $shipment)
	{
		$this->shipment = $shipment;
		$shipment->addShipmentItem($this);
		
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
	 * Set poItem
	 *
	 * @param \Ach\PoManagerBundle\Entity\PoItem $poItem
	 * @return ShipmentItem
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
	 * Set invoice
	 *
	 * @param \Ach\PoManagerBundle\Entity\Invoice $invoice
	 * @return ShipmentItem
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
	 * Set createdDate
	 *
	 * @param \DateTime $createdDate
	 * @return ShipmentItem
	 */
	public function setCreatedDate($createdDate)
	{
		$this->createdDate = $createdDate;
	
		return $this;
	}

    Public function addShipmentBatch(\Ach\PoManagerBundle\Entity\ShipmentBatch $shipmentBatch)
    {
        $this->shipmentBatches[] = $shipmentBatch;
        $shipmentBatch->setShipmentItem($this);
        return $this;
    }

    public function removeShipmentBatch(\Ach\PoManagerBundle\Entity\ShipmentBatch $shipmentBatch)
    {
        $this->shipmentBatches->removeElement($shipmentBatch);
        $shipmentBatch->setShipmentItem(null);
    }

    public function getShipmentBatches()
    {
        return $this->shipmentBatches;
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
	 * Set qty
	 *
	 * @param integer $qty
	 * @return ShipmentItem
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
     * Constructor
     */
    public function __construct($shipment, $poItem, $qty)
    {
		//$this->createdDate = new \Datetime;
		$this->setShipment($shipment);
		$this->poItem = $poItem;
		$this->qty = $qty;
        $this->shipmentBatches = new \Doctrine\Common\Collections\ArrayCollection();
		
    }
}
