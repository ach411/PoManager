<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Rma
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\RmaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Rma
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
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\SerialNumber")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serialNum;

    private $serialNumF;

    /**
     * @var string
     *
     * @ORM\Column(name="custSerialNum", type="string", length=255, nullable=true)
     */
    private $custSerialNum;

    /**
     * @var string
     *
     * @ORM\Column(name="problemDescription", type="text", length=255, nullable=false)
     */
    private $problemDescription;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\ProblemCategory")
     * @ORM\JoinColumn(nullable=true)
     */
    private $problemCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="investigationResult", type="text", nullable=true)
     */
    private $investigationResult;

    /**
     * @var string
     *
     * @ORM\Column(name="correction", type="text", nullable=true)
     */
    private $correction;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\RepairStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $repairStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="receptionDate", type="datetime", nullable=true)
     */
    private $receptionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="repairDate", type="datetime", nullable=true)
     */
    private $repairDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="shippedBackDate", type="datetime", nullable=true)
     */
    private $shippedBackDate;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\RepairLocation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $repairLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="repairFindings", type="text", nullable=true)
     */
    private $repairFindings;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Shipment")
     * @ORM\JoinColumn(nullable=true)
     */
    private $shipment;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Invoice")
     * @ORM\JoinColumn(nullable=true)
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\PartReplacement", mappedBy="rma", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $partReplacements;

    /**
     * @var string
     *
     * @ORM\Column(name="contactEmail", type="string", length=255, nullable=false)
     */
    private $contactEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="rpoFilePath", type="string", length=255, nullable=true)
     */
    private $rpoFilePath;

    private $rpoFile;

    private $rpoFileTemp;

    /**
     * @var string
     *
     * @ORM\Column(name="custFilePath", type="string", length=255, nullable=true)
     */
    private $custFilePath;

    private $custFile;

    private $custFileTemp;
    

    private $rootPath;
    
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
     * @return Rma
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
     * Set serialNum
     *
     * @param \Ach\PoManagerBundle\Entity\SerialNumber $serialNum
     * @return Rma
     */
    public function setSerialNum(\Ach\PoManagerBundle\Entity\SerialNumber $serialNum)
    {
        $this->serialNum = $serialNum;
    
        return $this;
    }

    /**
     * Get serialNum
     *
     * @return \Ach\PoManagerBundle\Entity\SerialNumber
     */
    public function getSerialNum()
    {
        return $this->serialNum;
    }

    /**
     * Set custSerialNum
     *
     * @param string $custSerialNum
     * @return Rma
     */
    public function setCustSerialNum($custSerialNum)
    {
        $this->custSerialNum = $custSerialNum;
    
        return $this;
    }

    /**
     * Get custSerialNum
     *
     * @return string 
     */
    public function getCustSerialNum()
    {
        return $this->custSerialNum;
    }

    /**
     * Set problemCategory
     *
     * @param \Ach\PoManagerBundle\Entity\ProblemCategory $problemCategory
     * @return Rma
     */
    public function setProblemCategory(\Ach\PoManagerBundle\Entity\ProblemCategory $problemCategory)
    {
        $this->problemCategory = $problemCategory;
    
        return $this;
    }

    /**
     * Get problemCategory
     *
     * @return \Ach\PoManagerBundle\Entity\ProblemCategory
     */
    public function getProblemCategory()
    {
        return $this->problemCategory;
    }

    /**
     * Set investigationResult
     *
     * @param string $investigationResult
     * @return Rma
     */
    public function setInvestigationResult($investigationResult)
    {
        $this->investigationResult = $investigationResult;
    
        return $this;
    }

    /**
     * Get investigationResult
     *
     * @return string 
     */
    public function getInvestigationResult()
    {
        return $this->investigationResult;
    }

    /**
     * Set correction
     *
     * @param string $correction
     * @return Rma
     */
    public function setCorrection($correction)
    {
        $this->correction = $correction;
    
        return $this;
    }

    /**
     * Get correction
     *
     * @return string 
     */
    public function getCorrection()
    {
        return $this->correction;
    }

    /**
     * Set repairStatus
     *
     * @param \Ach\PoManagerBundle\Entity\RepairStatus $repairStatus
     * @return Rma
     */
    public function setRepairStatus(\Ach\PoManagerBundle\Entity\RepairStatus $repairStatus)
    {
        $this->repairStatus = $repairStatus;
    
        return $this;
    }

    /**
     * Get repairStatus
     *
     * @return \Ach\PoManagerBundle\Entity\RepairStatus
     */
    public function getRepairStatus()
    {
        return $this->repairStatus;
    }


    /**
     * @ORM\PrePersist
     */
    public function createDate()
    {
        $this->setCreationDate(new \Datetime());
    }
    
    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Rma
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    
        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set receptionDate
     *
     * @param \DateTime $receptionDate
     * @return Rma
     */
    public function setReceptionDate($receptionDate)
    {
        $this->receptionDate = $receptionDate;
    
        return $this;
    }

    /**
     * Get $receptionDate
     *
     * @return \DateTime 
     */
    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    /**
     * Set repairDate
     *
     * @param \DateTime $repairDate
     * @return Rma
     */
    public function setRepairDate($repairDate)
    {
        $this->repairDate = $repairDate;
    
        return $this;
    }

    /**
     * Get $repairDate
     *
     * @return \DateTime 
     */
    public function getRepairDate()
    {
        return $this->repairDate;
    }

    /**
     * Set shippedBackDate
     *
     * @param \DateTime $shippedBackDate
     * @return Rma
     */
    public function setShippedBackDate($shippedBackDate)
    {
        $this->shippedBackDate = $shippedBackDate;
    
        return $this;
    }

    /**
     * Get $shippedBackDate
     *
     * @return \DateTime 
     */
    public function getShippedBackDate()
    {
        return $this->shippedBackDate;
    }

    /**
     * Set repairLocation
     *
     * @param \Ach\PoManagerBundle\Entity\RepairLocation $repairLocation
     * @return Rma
     */
    public function setRepairLocation(\Ach\PoManagerBundle\Entity\RepairLocation $repairLocation)
    {
        $this->repairLocation = $repairLocation;
    
        return $this;
    }

    /**
     * Get repairLocation
     *
     * @return \Ach\PoManagerBundle\Entity\RepairLocation
     */
    public function getRepairLocation()
    {
        return $this->repairLocation;
    }

    /**
     * Set repairFindings
     *
     * @param string $repairFindings
     * @return Rma
     */
    public function setRepairFindings($repairFindings)
    {
        $this->repairFindings = $repairFindings;
    
        return $this;
    }

    /**
     * Get repairFindings
     *
     * @return string 
     */
    public function getRepairFindings()
    {
        return $this->repairFindings;
    }

    /**
     * Set shipment
     *
     * @param \Ach\PoManagerBundle\Entity\Shipment $shipment
     * @return Rma
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
     * @return Rma
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
     * Set comment
     *
     * @param string $comment
     * @return Rma
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

    public function __construct($rootPath = null)
    {
        $this->partReplacements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rootPath = $rootPath;
    }

    public function addPartReplacement(\Ach\PoManagerBundle\Entity\PartReplacement $partReplacement)
    {
        $this->partReplacements[] = $partReplacement;
        $partReplacement->setRma($this);
        echo 'hello';
        return $this;
    }

    public function removePartReplacement(\Ach\PoManagerBundle\Entity\PartReplacement $partReplacement)
    {
        $this->partReplacements->removeElement($partReplacement);
    }

    public function getPartReplacements()
    {
        return $this->partReplacements;
    }
    
    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     * @return Rma
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
    
        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string 
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }
    
    /**
     * Set serialNumF
     *
     * @param string $serialNumF
     * @return Rma
     */
    public function setSerialNumF($serialNumF)
    {
        $this->serialNumF = $serialNumF;
    
        return $this;
    }

    /**
     * Get serialNumF
     *
     * @return string 
     */
    public function getSerialNumF()
    {
        return $this->serialNumF;
    }

    /**
     * Set problemDescription
     *
     * @param string $problemDescription
     * @return Rma
     */
    public function setProblemDescription($problemDescription)
    {
        $this->problemDescription = $problemDescription;
    
        return $this;
    }

    /**
     * Get problemDescription
     *
     * @return string 
     */
    public function getProblemDescription()
    {
        return $this->problemDescription;
    }
    	
	/**
     * Set rootPath
     *
     * @param string $rootPath
     * @return Bpo
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    
        return $this;
    }

    /**
     * Get rootPath
     *
     * @return string 
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * Set rpoFilePath
     *
     * @param string $rpoFilePath
     * @return Bpo
     */
    public function setRpoFilePath($rpoFilePath)
    {
		//echo 'Set file path';
        $this->rpoFilePath = $rpoFilePath;
    
        return $this;
    }

    /**
     * Get rpoFilePath
     *
     * @return string 
     */
    public function getRpoFilePath()
    {
        return $this->rpoFilePath;
    }

	/**
     * Set rpoFile
     *
     * @param \UploadedFile $rpoFile
     * @return Rma
     */
    public function setRpoFile(UploadedFile $rpoFile = null)
    {
		//echo '-- SET RPOFILE --';
        $this->rpoFile = $rpoFile;
    
		// if a rpoFile already existed
		if($this->rpoFilePath !== null)
		{
			$this->RpoFileTemp = $this->rpoFilePath;
		}
	
        return $this;
    }

    /**
     * Get rpoFile
     *
     * @return rpoFile
     */
    public function getRpoFile()
    {
		return $this->rpoFile;
    }

	/**
	* @ORM\PrePersist()
	* @ORM\PreUpdate()
	*/
	public function preUploadRpoFile()
	{
		//echo 'preupload';
		// if no file has been set
		if($this->rpoFile === null)
		{
			//echo '---NO FILE---';
			return;
		}
		
		$filename = 'RPO_' . ($this->getNum()) . '.pdf';
		$this->setRpoFilePath($filename);
		
		//echo $this->getFilePath();
		
	}
	
	/**
	* @ORM\PostPersist()
	* @ORM\PostUpdate()
	*/
	public function uploadRpoFile()
    {
		//echo 'upload';
		// if no file has been set
		if($this->rpoFile === null)
		{
			return;
		}
		
		// if old file existed, delete it
		if($this->rpoFileTemp !== null)
		{
			$oldFile = $this->rootPath . '/' . $this->rpoFileTemp;
			
			if(file_exists($oldFile))
			{
				unlink($oldFile);
			}
		}
		
		// $filename = 'BPO_' . ($this->getNum()) . '.pdf';
		// $this->file->move($this->rootPath, $filename);
		// $this->setFilePath($filename);
		
		//echo $this->rootPath;
		
		$this->rpoFile->move($this->rootPath, $this->rpoFilePath);
    }

    /**
     * Set custFilePath
     *
     * @param string $custFilePath
     * @return Bpo
     */
    public function setCustFilePath($custFilePath)
    {
		//echo 'Set file path';
        $this->custFilePath = $custFilePath;
    
        return $this;
    }

    /**
     * Get custFilePath
     *
     * @return string 
     */
    public function getCustFilePath()
    {
        return $this->custFilePath;
    }

	/**
     * Set custFile
     *
     * @param \UploadedFile $custFile
     * @return Rma
     */
    public function setCustFile(UploadedFile $custFile = null)
    {
		//echo '-- SET CUSTFILE --';
        $this->custFile = $custFile;
    
		// if a custFile already existed
		if($this->custFilePath !== null)
		{
			$this->CustFileTemp = $this->custFilePath;
		}
	
        return $this;
    }

    /**
     * Get custFile
     *
     * @return custFile
     */
    public function getCustFile()
    {
		return $this->custFile;
    }

	/**
	* @ORM\PrePersist()
	* @ORM\PreUpdate()
	*/
	public function preUploadCustFile()
	{
		//echo 'preupload';
		// if no file has been set
		if($this->custFile === null)
		{
			//echo '---NO FILE---';
			return;
		}
		
		$filename = 'CUST_' . ($this->getNum()) . '.pdf';
		$this->setCustFilePath($filename);
		
		//echo $this->getFilePath();
		
	}
	
	/**
	* @ORM\PostPersist()
	* @ORM\PostUpdate()
	*/
	public function uploadCustFile()
    {
		//echo 'upload';
		// if no file has been set
		if($this->custFile === null)
		{
			return;
		}
		
		// if old file existed, delete it
		if($this->custFileTemp !== null)
		{
			$oldFile = $this->rootPath . '/' . $this->custFileTemp;
			
			if(file_exists($oldFile))
			{
				unlink($oldFile);
			}
		}
		
		// $filename = 'BPO_' . ($this->getNum()) . '.pdf';
		// $this->file->move($this->rootPath, $filename);
		// $this->setFilePath($filename);
		
		//echo $this->rootPath;
		
		$this->custFile->move($this->rootPath, $this->custFilePath);
    }

}
