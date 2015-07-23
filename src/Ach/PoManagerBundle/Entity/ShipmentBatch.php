<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ShipmentBatch
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\ShipmentBatchRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ShipmentBatch
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
     * @ORM\Column(name="num", type="string", length=255, nullable=true)
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="productName", type="string", length=255, nullable=true)
     */
    private $productName;

    /**
     * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\SerialNumber", mappedBy="shipmentBatch")
     * @ORM\JoinColumn(nullable=true)
     */
    private $serialNumbers;

    /**
     * @var boolean
     *
     * @ORM\Column(name="waitingForRemoval", type="boolean")
     */
    private $waitingForRemoval;

  	/**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Shipment")
     * @ORM\JoinColumn(nullable=true)
     */
    private $shipment;

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
     * @ORM\Column(name="filePath", type="string", length=255, nullable=true)
     */
    private $filePath;
	
	private $rootpath;
	
	private $tempFile;
	
	private $file;

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
     * Set num
     *
     * @param string $num
     * @return ShipmentBatch
     */
    public function setNum($num)
    {
        $this->num = $num;
    
        return $this;
    }

    /**
     * Get num
     *
     * @return string 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set productName
     *
     * @param string $productName
     * @return ShipmentBatch
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    
        return $this;
    }

    /**
     * Get ProductName
     *
     * @return string 
     */
    public function getProductName()
    {
        return $this->productName;
    }

    public function addSerialNumber(\Ach\PoManagerBundle\Entity\SerialNumber $serialNumber)
    {
        $this->serialNumbers[] = $serialNumber;
        $serialNumber->setShipmentBatch($this);
        return $this;
    }

    public function removeSerialNumber(\Ach\PoManagerBundle\Entity\SerialNumber $serialNumber)
    {
        $this->serialNumbers->removeElement($serialNumber);
        $serialNumber->setShipmentBatch(null);
    }

    public function getSerialNumbers()
    {
        return $this->serialNumbers;
    }

    /**
     * Set shipment
     *
     * @param \Ach\PoManagerBundle\Entity\Shipment $shipment
     * @return ShipmentBatch
     */
    public function setShipment(\Ach\PoManagerBundle\Entity\Shipment $shipment = null)
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
     * Set waitingForRemoval
     *
     * @param boolean $waitingForRemoval
     * @return ShipmentBatch
     */
    public function setWaitingForRemoval($waitingForRemoval)
    {
        $this->waitingForRemoval = $waitingForRemoval;
    
        return $this;
    }

    /**
     * Get waitingForRemoval
     *
     * @return boolean 
     */
    public function getWaitingForRemoval()
    {
        return $this->waitingForRemoval;
    }

    /**
	 * Set comment
	 *
	 * @param string $comment
	 * @return ShipmentBatch
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
     * @return ShipmentBatch
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
     * @return ShipmentBatch
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
     * @param String $filePath
     * @return ShipmentBatch
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    
        return $this;
    }

    /**
     * Get filePath
     *
     * @return String
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set file
     *
     * @param \UploadedFile $file
     * @return ShipmentBatch
     */
    public function setFile(UploadedFile $file = null)
    {
		//echo '-- SET FILE --';
        $this->file = $file;
    
		// if a file already existed
		if($this->filePath !== null)
		{
			$this->tempFile = $this->filePath;
		}
	
        return $this;
    }

    /**
     * Get file
     *
     * @return file
     */
    public function getFile()
    {
		return $this->file;
    }
	
	/**
	* @ORM\PrePersist()
	* @ORM\PreUpdate()
	*/
	public function preUploadFile()
	{
		//echo 'preupload';
		// if no file has been set
		if($this->file === null)
		{
			//echo '---NO FILE---';
			return;
		}
		
		$filename = $this->file->getClientOriginalName();;
		$this->setFilePath($filename);
		
		//echo $this->getFilePath();
		
	}
	
	/**
	* @ORM\PostPersist()
	* @ORM\PostUpdate()
	*/
	public function uploadFile()
    {
		//echo 'upload';
		// if no file has been set
		if($this->file === null)
		{
			return;
		}
		
		// if old file existed, delete it
		if($this->tempFile !== null)
		{
			$oldFile = $this->rootPath . '/' . $this->tempFile;
			
			if(file_exists($oldFile))
			{
				unlink($oldFile);
			}
		}
		
		//echo $this->rootPath;
		
		$this->file->move($this->rootPath, $this->filePath);
    }

    /**
     * Constructor
     */
    public function __construct($rootPath = null)
    {
        $this->rootPath = $rootPath;
        $this->waitingForRemoval = false;

        $this->serialNumbers = new \Doctrine\Common\Collections\ArrayCollection();
		//echo $this->rootPath;
    }
}