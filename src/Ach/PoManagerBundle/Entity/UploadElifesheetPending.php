<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UploadElifesheetPending
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\UploadElifesheetPendingRepository")
 */
class UploadElifesheetPending
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
	 * @ORM\OneToOne(targetEntity="Ach\PoManagerBundle\Entity\ShipmentItem")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $shipmentItem;

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
     * @return UploadElifesheetPending
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
}