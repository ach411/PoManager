<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Bpo
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\BpoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Bpo
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
     * @ORM\OneToOne(targetEntity="Ach\PoManagerBundle\Entity\Bpo")
     *
     */
    private $pairedBpo;
	
	/**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Revision")
     * @ORM\JoinColumn(nullable=false)
     */
    private $revision;

	private $descriptionF;
	
	private $revisionF;
    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Price")
     * @ORM\JoinColumn(nullable=false)
     */
    private $price;
	
	private $priceF;

    /**
     * @var integer
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;
	
	private $releasedQty;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date", nullable=true)
     */
    private $endDate;

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
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="buyerEmail", type="string", length=255, nullable=true)
     */
    private $buyerEmail;

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
     * @return Bpo
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
     * Set qty
     *
     * @param integer $qty
     * @return Bpo
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
     * Set releasedQty
     *
     * @param integer $releasedQty
     * @return Bpo
     */
    public function setReleasedQty($releasedQty)
    {
        $this->releasedQty = $releasedQty;
    
        return $this;
    }

    /**
     * Get releasedQty
     *
     * @return integer 
     */
    public function getReleasedQty()
    {
        return $this->releasedQty;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Bpo
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Bpo
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set filePath
     *
     * @param string $filePath
     * @return Bpo
     */
    public function setFilePath($filePath)
    {
		echo 'Set file path';
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
     * Set file
     *
     * @param \UploadedFile $file
     * @return Bpo
     */
    public function setFile(UploadedFile $file = null)
    {
		echo '-- SET FILE --';
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
		echo 'preupload';
		// if no file has been set
		if($this->file === null)
		{
			echo '---NO FILE---';
			return;
		}
		
		$filename = 'BPO_' . ($this->getNum()) . '.pdf';
		$this->setFilePath($filename);
		
		echo $this->getFilePath();
		
	}
	
	/**
	* @ORM\PostPersist()
	* @ORM\PostUpdate()
	*/
	public function uploadFile()
    {
		echo 'upload';
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
		
		// $filename = 'BPO_' . ($this->getNum()) . '.pdf';
		// $this->file->move($this->rootPath, $filename);
		// $this->setFilePath($filename);
		
		echo $this->rootPath;
		
		$this->file->move($this->rootPath, $this->filePath);
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Bpo
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
     * Set buyerEmail
     *
     * @param string $buyerEmail
     * @return Bpo
     */
    public function setBuyerEmail($buyerEmail)
    {
        $this->buyerEmail = $buyerEmail;
    
        return $this;
    }

    /**
     * Get buyerEmail
     *
     * @return string 
     */
    public function getBuyerEmail()
    {
        return $this->buyerEmail;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return Bpo
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
     * @return Bpo
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
     * Set pairedBpo
     *
     * @param \Ach\PoManagerBundle\Entity\Bpo $pairedBpo
     * @return Bpo
     */
    public function setPairedBpo(\Ach\PoManagerBundle\Entity\Bpo $pairedBpo = null)
    {
        $this->pairedBpo = $pairedBpo;
    
        return $this;
    }

    /**
     * Get pairedBpo
     *
     * @return \Ach\PoManagerBundle\Entity\Bpo 
     */
    public function getPairedBpo()
    {
        return $this->pairedBpo;
    }
	
	/**
     * Set revision
     *
     * @param \Ach\PoManagerBundle\Entity\Revision $revision
     * @return Bpo
     */
    public function setRevision(\Ach\PoManagerBundle\Entity\Revision $revision = null)
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
     * Set descriptionF
     *
     * @param string $descriptionF
     * @return Bpo
     */
    public function setDescriptionF($descriptionF)
    {
        $this->descriptionF = $descriptionF;
    
        return $this;
    }

    /**
     * Get descriptionF
     *
     * @return string 
     */
    public function getDescriptionF()
    {
        return $this->descriptionF;
    }
	
	/**
     * Set revisionF
     *
     * @param integer $revisionF
     * @return Bpo
     */
    public function setRevisionF($revisionF = null)
    {
        $this->revisionF = $revisionF;
    
        return $this;
    }

    /**
     * Get revisionF
     *
     * @return \Ach\PoManagerBundle\Entity\Revision 
     */
    public function getRevisionF()
    {
        return $this->revisionF;
    }

    /**
     * Set price
     *
     * @param \Ach\PoManagerBundle\Entity\Price $price
     * @return Bpo
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
     * Set priceF
     *
     * @param float $priceF
     * @return Bpo
     */
    public function setPriceF($priceF)
    {
        $this->priceF = $priceF;
    
        return $this;
    }

    /**
     * Get priceF
     *
     * @return \Ach\PoManagerBundle\Entity\PriceF 
     */
    public function getPriceF()
    {
        return $this->priceF;
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