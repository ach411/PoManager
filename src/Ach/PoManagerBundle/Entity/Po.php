<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Po
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\PoRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Po
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
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Bpo")
     * 
     */
    private $bpo;
	
	private $isBpo;

    /**
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="relNum", type="string", length=255, nullable=true)
     */
    private $relNum;

    /**
     * @var string
     *
     * @ORM\Column(name="filePath", type="string", length=255, nullable=true)
     */
    private $filePath;

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
     *
     * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\PoItem", mappedBy="po", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $poItems;

    private $file;

    private $totalAmount;

    private $currency;

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
     * @return Po
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
     * Set relNum
     *
     * @param string $relNum
     * @return Po
     */
    public function setRelNum($relNum)
    {
        $this->relNum = $relNum;
    
        return $this;
    }

    /**
     * Get relNum
     *
     * @return string 
     */
    public function getRelNum()
    {
        return $this->relNum;
    }

    /**
     * Set filePath
     *
     * @param string $filePath
     * @return Po
     */
    public function setFilePath($filePath)
    {
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
     * Set comment
     *
     * @param string $comment
     * @return Po
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
     * @return Po
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
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     * @return Po
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
     * Set bpo
     *
     * @param \Ach\PoManagerBundle\Entity\Bpo $bpo
     * @return Po
     */
    public function setBpo(\Ach\PoManagerBundle\Entity\Bpo $bpo = null)
    {
        $this->bpo = $bpo;
    
        return $this;
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
     * Set isBpo
     *
     * @param boolean $isBpo
     * @return Po
     */
    public function setIsBpo($isBpo)
    {
        $this->isBpo = $isBpo;
    
        return $this;
    }

    /**
     * Get isBpo
     *
     * @return boolean
     */
    public function getIsBpo()
    {
        //return $this->isBpo;
		if(isset($this->isBpo))
		{
			return $this->isBpo;
		}
		else
        {
			if(empty($this->bpo))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
    }
	
    /**
     * Set file
     *
     * @param \UploadedFile $file
     * @return Po
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    
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
     * Set totalAmount
     *
     * @param string $totalAmount
     * @return Po
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    
        return $this;
    }

    /**
     * Get totalAmount
     *
     * @return string
     */
    public function getTotalAmount()
    {
	return $this->totalAmount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Po
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
	return $this->currency;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->poItems = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add poItems
     *
     * @param \Ach\PoManagerBundle\Entity\PoItem $poItem
     * @return Po
     */
    public function addPoItem(\Ach\PoManagerBundle\Entity\PoItem $poItem)
    {
        $this->poItems[] = $poItem;
	$poItem->setPo($this);
        return $this;
    }

    /**
     * Remove poItems
     *
     * @param \Ach\PoManagerBundle\Entity\PoItem $poItem
     */
    public function removePoItem(\Ach\PoManagerBundle\Entity\PoItem $poItem)
    {
        $this->poItems->removeElement($poItem);
    }

    /**
     * Get poItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPoItems()
    {
        return $this->poItems;
    }

    public function setItems($poItems)
    {
	foreach ($poItems as $poIt)
	{
	    if (is_null($poIt->getPo()) )
	    {
		$poIt->setPo($this);
	    }
	}
    }

    public function parsePdfFile()
    {
	// get PDF parser object
	$parser = new \Smalot\PdfParser\Parser();

	// parse file
	$pdf    = $parser->parseFile($this->file);

	// get text version of PDF
	$po_text = $pdf->getText();

	// remove all unpredictable space characters
	$subject = preg_replace('/\s+/', '', $po_text);
	//print_r($po_text);
	//echo '<br>';
	//echo '<br>';

	// determine if PO or BPO release
	if (preg_match('/\ABlanket Release/', $po_text))
	{
		//echo 'BPO release <br>';
		$this->setIsBpo(true);
	    //preg_match('/^BPO Number-Release Number \d{+} - (\d{+})/', $po_text, $releaseNumber);
	    preg_match('/BPO Number-Release Number (\w+) - (\d+)/', $po_text, $infoNumber);
	    $this->num = $infoNumber[1];
	    $this->relNum = $infoNumber[2];
	    //print_r($infoNumber);
	}
	else
	{
	    if(preg_match('/\APurchase Order/', $po_text))
	    {
		//echo 'Purchase Order <br>';
		$this->setIsBpo(false);
		//print_r($po_text);
		//preg_match('/PO Number (\w+)/', $po_text, $infoNumber);
		preg_match('/PO Number (.+)PO Revision/', $po_text, $infoNumber);
		//$this->num = $infoNumber[1];
		$this->num = preg_replace('/\s+/', '', $infoNumber[1]);
		$this->relNum = "N/A";
		
		//print_r($infoNumber);
	    }
	}
	//echo '<br>';

	// extract buyer email address
	//preg_match('/Buyer.Name:(.+)\,(.+)Buyer.Email:(.+@STRYKER.COM)/U', $po_text, $buyerInfo);
	if (preg_match('/BuyerEmail:(.+@STRYKER.COM)/U', $subject, $buyerInfo))
	{
	    //print_r($buyerInfo);
	    $this->buyerEmail = $buyerInfo[1];
	}
	//print_r($this->buyerEmail);
	//echo '<br>';

	// extract total amount and currency of PO
	if (preg_match('/TOTAL:?([A-Z]{3})([0-9]+[\.0-9]*)Entered/', $subject, $totalAmountInfo))
	{
	    //echo '<br>';
	    //print_r($totalAmountInfo);
	    $this->totalAmount = $totalAmountInfo[2];
	    $this->currency = $totalAmountInfo[1];
	}	

	// extract items
	$pattern = "#([0-9]{10}|P[0-9]{5})(SERVICE|ACCESSORIES|VIDEO|ICTS)(.*?)NOTE:([A-Z]+)([0-9]+?)([0-9]{2}-[A-Z]{3}-[0-9]{2}){1,2}([0-9.]+?)EACH#s";
        preg_match_all($pattern, $subject, $matches);
	//print_r($matches);
	$shopping_list = array(array());

	for($iteration=0; $iteration<sizeof($matches[0]); $iteration++)
	{
//		$poItem_it = new \Ach\PoManagerBundle\Entity\PoItem();

		$shopping_list[$iteration]['SKPN'] = $matches[1][$iteration];
		$shopping_list[$iteration]['SKREV'] = $matches[4][$iteration];
		$shopping_list[$iteration]['DESC'] = $matches[3][$iteration];
		$shopping_list[$iteration]['QTY'] = $matches[5][$iteration];
		//$shopping_list[$iteration]['NBD'] = $matches[6][$iteration];
		$shopping_list[$iteration]['NBD'] = date('Y-m-d', strtotime($matches[6][$iteration]));
		$shopping_list[$iteration]['PRICE'] = $matches[7][$iteration];
/*
		$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Product');

		$product = $repository->findOneBy(array('custPn' => $shopping_list[$iteration]['SKPN']));

		$poItem_it
			->setLineNum($iteration+1)
			->setDescription($shopping_list[$iteration]['DESC'])
			->setQty($shopping_list[$iteration]['QTY'])
			->setDueDate(new \DateTime($shopping_list[$iteration]['NBD']))
		;
		$this->addPoItem($poItem_it);
*/		
	}
	// print_r($shopping_list);
	return($shopping_list);

    }


    public function uploadFile($rootdir)
    {
	$filename = ($this->getRelNum() == 'N/A') ? ('PO_' . $this->getNum() . '.pdf') : ('BPO_' . $this->getNum() . '_rel_' . $this->getRelNum() . '.pdf');
	$this->file->move($rootdir, $filename);
	$this->setFilePath($filename);
    }

}