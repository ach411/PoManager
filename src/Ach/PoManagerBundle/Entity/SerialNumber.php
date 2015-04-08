<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SerialNumber
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\SerialNumberRepository")
 */
class SerialNumber
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
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\ShipmentItem")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $shipmentItem;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="serialNumber", type="string", length=255, nullable=true)
	 */
	private $serialNumber;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="macAddress", type="string", length=12, nullable=true)
	 */
	private $macAddress;

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
	 * Set shipmentItem
	 *
	 * @param \Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem
	 * @return SerialNumber
	 */
	public function setShipmentItem(\Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem)
	{
		$this->shipmentItem = $shipmentItem;
	
		return $this;
	}

	/**
	 * Get shipmentItem
	 *
	 * @return \Ach\PoManagerBundle\Entity\ShipmentItem
	 */
	public function getShipmentItem()
	{
		return $this->shipmentItem;
	}

	/**
	 * Set serialNumber
	 *
	 * @param string $serialNumber
	 * @return SerialNumber
	 */
	public function setSerialNumber($serialNumber)
	{
		$this->serialNumber = $serialNumber;
	
		return $this;
	}

	/**
	 * Get serialNumber
	 *
	 * @return string 
	 */
	public function getSerialNumber()
	{
		return $this->serialNumber;
	}

	/**
	 * Set macAddress
	 *
	 * @param string $macAddress
	 * @return SerialNumber
	 */
	public function setMacAddress($macAddress)
	{
		$this->macAddress = $macAddress;
	
		return $this;
	}

	/**
	 * Get macAddress
	 *
	 * @return string 
	 */
	public function getMacAddress()
	{
		return $this->macAddress;
	}

	/**
	 * Set comment
	 *
	 * @param string $comment
	 * @return SerialNumber
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
