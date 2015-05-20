<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Invoice
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice
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
	 * @var string
	 *
	 * @ORM\Column(name="filePath", type="string", length=255, nullable=true)
	 */
	private $filePath;
	
	private $file;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="proformaInvoicePath", type="string", length=255, nullable=true)
	 */
	private $proformaInvoicePath;
	
	private $proformaFile;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="invoiceDate", type="date", nullable=true)
	 */
	private $invoiceDate;

	private $invoiceDateF;
	
	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="createdDate", type="date", nullable=true)
	 */
	private $createdDate;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="comment", type="text", nullable=true)
	 */
	private $comment;
	
	/**
	 * @ORM\OneToMany(targetEntity="Ach\PoManagerBundle\Entity\ShipmentItem", mappedBy="invoice")
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
	 * Set num
	 *
	 * @param string $num
	 * @return Invoice
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
	 * Set filePath
	 *
	 * @param string $filePath
	 * @return Invoice
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
	* Set file
	*
	* @param \UploadedFile $file
	* @return Invoice
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
	
	public function parsePdfFile()
	{
		// get PDF parser object
		$parser = new \Smalot\PdfParser\Parser();

		// parse file
		$pdf = $parser->parseFile($this->file);

		// get text version of PDF
		$po_text = $pdf->getText();
		
		// remove all unpredictable space characters
		$subject = preg_replace('/\s+/', '', $po_text);
		
		//echo $po_text;
		
		preg_match('/date:([0-9]{2}-[a-z]{3}-[0-9]{4}|[0-1][0-9]\/[0-3][0-9]\/[0-9]{4})/i', $subject, $dateInfo);
		//echo $dateInfo[1] . "\r\n";
		if(empty($dateInfo[1]))
		{
			throw $this->createNotFoundException('No date found or invalid date format in the invoice document');
		}
		$this->invoiceDate = new \DateTime($dateInfo[1]);
		$this->invoiceDateF = $dateInfo[1];
		//echo $this->invoiceDate->format('Y-m-d') . "\r\n";
		
		preg_match('/INVOICE#(F[0-9]{9})/i', $subject, $invoiceNumInfo);
		//echo $invoiceNumInfo[1] . "\r\n";
		if(empty($invoiceNumInfo[1]))
		{
			throw $this->createNotFoundException('No invoice number found or invalid number format in the invoice document');
		}
		$this->num = $invoiceNumInfo[1];
		
		return $this;
		
	}
	
	public function uploadFile($rootdir)
	{
		// move the uploaded file to final location
		$this->file->move($rootdir, $this->num . '.pdf');
		// filename = Invoice number = filepath
		$this->setFilePath($this->num . '.pdf');
		
		return $this;
	}
	
	/**
	 * Set proformaInvoicePath
	 *
	 * @param string $proformaInvoicePath
	 * @return Invoice
	 */
	public function setProformaInvoicePath($proformaInvoicePath)
	{
		$this->proformaInvoicePath = $proformaInvoicePath;
	
		return $this;
	}

	/**
	 * Get proformaInvoicePath
	 *
	 * @return string 
	 */
	public function getProformaInvoicePath()
	{
		return $this->proformaInvoicePath;
	}
	
	/**
	* Set proformaFile
	*
	* @param \UploadedFile $proformaFile
	* @return Invoice
	*/
	public function setProformaFile(UploadedFile $proformaFile = null)
	{
		$this->proformaFile = $proformaFile;
		
		return $this;
	}
	
	/**
	* Get proformaFile
	*
	* @return proformaFile
	*/
	public function getProformaFile()
	{
		return $this->proformaFile;
	}
	
	public function uploadProformaFile($rootdir)
	{
		// move the uploaded file to final location
		$this->file->move($rootdir, $this->num);
		// filename = Invoice number = filepath
		$this->setFilePath($this->num);
		
		return $this;
	}
	
	/**
	 * Set invoiceDate
	 *
	 * @param \DateTime $invoiceDate
	 * @return Invoice
	 */
	public function setInvoiceDate($invoiceDate)
	{
		$this->invoiceDate = $invoiceDate;
	
		return $this;
	}

	/**
	 * Get invoiceDate
	 *
	 * @return \DateTime 
	 */
	public function getInvoiceDate()
	{
		return $this->invoiceDate;
	}
	
		/**
	 * Set invoiceDateF
	 *
	 * @param string $invoiceDateF
	 * @return Invoice
	 */
	public function setInvoiceDateF($invoiceDateF)
	{
		$this->invoiceDateF = $invoiceDateF;
	
		return $this;
	}

	/**
	 * Get invoiceDateF
	 *
	 * @return string
	 */
	public function getInvoiceDateF()
	{
		return $this->invoiceDateF;
	}
	
	/**
	 * Set createdDate
	 *
	 * @param \DateTime $createdDate
	 * @return Invoice
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
	 * Set comment
	 *
	 * @param string $comment
	 * @return Invoice
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
	
	public function __construct(UploadedFile $file = null, $comment = null)
	{
		$this->shipmentItems = new \Doctrine\Common\Collections\ArrayCollection();
		$this->file = $file;
		$this->comment = $comment;
	}
	
	public function addShipmentItem(\Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem)
	{
		$this->shipmentItems[] = $shipmentItem;
		$shipmentItem->setInvoice($this);
		return $this;
	}
	
	public function removeShipmentItem(\Ach\PoManagerBundle\Entity\ShipmentItem $shipmentItem)
	{
		$this->shipmentItems->removeElement($shipmentItem);
		$shipmentItem->setInvoice(null);
		return $this;
	}
	
	public function getShipmentItems()
	{
		return $this->shipmentItems;
	}
}
