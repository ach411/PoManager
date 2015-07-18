<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * SerialNumber
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\SerialNumberRepository")
 * @ORM\HasLifecycleCallbacks()
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
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $shipmentItem;

    /**
	 * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\ShipmentBatch")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $shipmentBatch;
    
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
	 * @ORM\Column(name="certificateFileName", type="string", length=255, nullable=true)
	 */
	private $certificateFileName;
    
	/**
	 * @var string
	 *
	 * @ORM\Column(name="comment", type="text", nullable=true)
	 */
	private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modifiedDate", type="datetime", nullable=true)
     */
    private $modifiedDate;

    /**
	 * @var string
	 *
	 * @ORM\Column(name="filePath", type="string", nullable=true)
	 */
	private $filePath;
	
	//private $rootpath;
	
	//private $tempFile;
	
   	//private $file;

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
	 * Set shipmentBatch
	 *
	 * @param \Ach\PoManagerBundle\Entity\ShipmentBatch $shipmentBatch
	 * @return SerialNumber
	 */
	public function setShipmentBatch(\Ach\PoManagerBundle\Entity\ShipmentBatch $shipmentBatch)
	{
		$this->shipmentBatch = $shipmentBatch;
	
		return $this;
	}

	/**
	 * Get shipmentBatch
	 *
	 * @return \Ach\PoManagerBundle\Entity\ShipmentBatch
	 */
	public function getShipmentBatch()
	{
		return $this->shipmentBatch;
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
	 * Set certificateFileName
	 *
	 * @param string $certificateFileName
	 * @return SerialNumber
	 */
	public function setCertificateFileName($certificateFileName)
	{
		$this->certificateFileName = $certificateFileName;
	
		return $this;
	}

	/**
	 * Get certificateFileName
	 *
	 * @return string 
	 */
	public function getCertificateFileName()
	{
		return $this->certificateFileName;
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

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return SerialNumber
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
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     * @return SerialNumber
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
    
        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime 
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
    * @ORM\PreUpdate
    */
    public function modificationDate()
    {
	$this->setModifiedDate(new \Datetime());
    }
    
    /**
     * Set filePath
     *
     * @param string $filePath
     * @return SerialNumber
     */
    public function setFilePath($filePath)
    {
		//echo 'Set file path';
        $this->filePath = $filePath;
    
        return $this;
    }

    /**
     * Get filePath
     *
     * @return string 
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Constructor
     */
     public function __construct($rootPath = null)
    {
        $this->rootPath = $rootPath;
		//echo $this->rootPath;
    }
    
}
