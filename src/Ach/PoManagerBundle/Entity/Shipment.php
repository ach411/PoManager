<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shipment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\ShipmentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Shipment
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
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Carrier")
	 *
	 */
	private $carrier;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="shippingDate", type="date", nullable=true)
	 */
	private $shippingDate;
	
	// special variable for non DateTime form field
	private $shippingDateF;

	 /**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdDate", type="datetime", nullable=true)
	 */
	private $createdDate;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="trackingNum", type="string", length=255, nullable=true)
	 */
	private $trackingNum;

	/**
	 * @var string
	 *
	 * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\ShipmentItem", mappedBy="shipment")
	 */
	private $shipmentItems;

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
	 * Set shippingDate
	 *
	 * @param \DateTime $shippingDate
	 * @return Shipment
	 */
	public function setShippingDate($shippingdate)
	{
		$this->shippingdate = $shippingDate;
	
		return $this;
	}

	/**
	 * Get shippingDate
	 *
	 * @return \DateTime 
	 */
	public function getShippingDate()
	{
		return $this->shippingDate;
	}
	
	/**
	 * Set shippingDateF
	 *
	 * @param string $shippingDateF
	 * @return Shipment
	 */
	public function setShippingDateF($shippingDateF)
	{
		$this->shippingDateF = $shippingDateF;
	
		return $this;
	}

	/**
	 * Get shippingDate
	 *
	 * @return string
	 */
	public function getShippingDateF()
	{
		return $this->shippingDateF;
	}
	
	/**
	 * Set createdDate
	 *
	 * @param \DateTime $createdDate
	 * @return Shipment
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
	 * Set trackingNum
	 *
	 * @param string $trackingNum
	 * @return Shipment
	 */
	public function setTrackingNum($trackingNum)
	{
		$this->trackingNum = $trackingNum;
	
		return $this;
	}

	/**
	 * Get trackingNum
	 *
	 * @return string 
	 */
	public function getTrackingNum()
	{
		return $this->trackingNum;
	}

	/**
	 * Set carrier
	 *
	 * @param \Ach\PoManagerBundle\Entity\Carrier $carrier
	 * @return Shipment
	 */
	public function setCarrier(\Ach\PoManagerBundle\Entity\Carrier $carrier = null)
	{
		$this->carrier = $carrier;
	
		return $this;
	}

	/**
	 * Get carrier
	 *
	 * @return \Ach\PoManagerBundle\Entity\Carrier 
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}
	
	/**
	 * addShipmentItem
	 */
	public function addShipmentItem(\Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem)
	{
		$this->shipmentItems[] = $shipmentItem;
		//$shipmentItem->setShipment($this);
		return $this;
	}
	
	/**
	 * removeShipmentItem
	 */
	public function removeShipmentItem(\Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem)
	{
		$this->shipmentItems->removeElement($shipmentItem);
	}
	
	/**
	 * getShipmentItems
	 */
	public function getShipmentItems()
	{
		return $this->shipmentItems;
	}
	
	/**
     * Constructor
     */
    public function __construct($trackingNum = null, $shippingDate = null, $carrier = null)
    {
		//$this->createdDate = new \Datetime;
		$this->trackingNum = $trackingNum;
		$this->shippingDate = $shippingDate;
		$this->shippingDateF = date_format(new \DateTime('now'),'Y-m-d');
		$this->carrier = $carrier;
		$this->shipmentItems = new \Doctrine\Common\Collections\ArrayCollection();
		
    }
}