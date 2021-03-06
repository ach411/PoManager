<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\ProductRepository")
 */
class Product
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
     * @ORM\Column(name="pn", type="string", length=255, nullable=true)
     */
    private $pn;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Customer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="custPn", type="string", length=255, nullable=true)
     */
    private $custPn;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Price")
     * @ORM\JoinColumn(nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Ach\PoManagerBundle\Entity\Category")
     *
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\Unit")
     * @ORM\JoinColumn(nullable=false)
     */
    private $unit;

    /**
     * @var integer
     *
     * @ORM\Column(name="moq", type="integer")
     */
    private $moq;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\ProdManager")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $prodManager;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\ShippingManager")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $shippingManager;

    /**
     * @ORM\ManyToOne(targetEntity="Ach\PoManagerBundle\Entity\BillingManager")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $billingManager;

    /**
     * @var string
     *
     * @ORM\Column(name="prodName", type="string", length=255, nullable=true)
     */
    private $prodName;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToMany(targetEntity="Ach\PoManagerBundle\Entity\Product", mappedBy="spareParts")
     **/
    private $partOfProducts;

    /**
     * @ORM\ManyToMany(targetEntity="Ach\PoManagerBundle\Entity\Product", inversedBy="partOfProducts")
     * @ORM\JoinTable(name="spare_parts",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="spare_parts_id", referencedColumnName="id")}
     *      )
     **/
    private $spareParts;

    /**
     * @var boolean
     *
     * @ORM\Column(name="elifesheet", type="boolean")
     */
    private $elifesheet;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
        $this->partOfProducts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spareParts = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set pn
     *
     * @param string $pn
     * @return Product
     */
    public function setPn($pn)
    {
        $this->pn = $pn;
    
        return $this;
    }

    /**
     * Get pn
     *
     * @return string 
     */
    public function getPn()
    {
        return $this->pn;
    }

    /**
     * Set customer
     *
     * @param \Ach\PoManagerBundle\Entity\Customer $customer
     * @return Product
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    
        return $this;
    }

    /**
     * Get customer
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set custPn
     *
     * @param string $custPn
     * @return Product
     */
    public function setCustPn($custPn)
    {
        $this->custPn = $custPn;
    
        return $this;
    }

    /**
     * Get custPn
     *
     * @return string 
     */
    public function getCustPn()
    {
        return $this->custPn;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set moq
     *
     * @param integer $moq
     * @return Product
     */
    public function setMoq($moq)
    {
        $this->moq = $moq;
    
        return $this;
    }

    /**
     * Get moq
     *
     * @return integer 
     */
    public function getMoq()
    {
        return $this->moq;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Product
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set price
     *
     * @param \Ach\PoManagerBundle\Entity\Price $price
     * @return Product
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
     * Add category
     *
     * @param \Ach\PoManagerBundle\Entity\Category $category
     * @return Product
     */
    public function addCategory(\Ach\PoManagerBundle\Entity\Category $category)
    {
        $this->category[] = $category;
    
        return $this;
    }

    /**
     * Remove category
     *
     * @param \Ach\PoManagerBundle\Entity\Category $category
     */
    public function removeCategory(\Ach\PoManagerBundle\Entity\Category $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set unit
     *
     * @param \Ach\PoManagerBundle\Entity\Unit $unit
     * @return Product
     */
    public function setUnit(\Ach\PoManagerBundle\Entity\Unit $unit)
    {
        $this->unit = $unit;
    
        return $this;
    }

    /**
     * Get unit
     *
     * @return \Ach\PoManagerBundle\Entity\Unit 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set prodManager
     *
     * @param \Ach\PoManagerBundle\Entity\ProdManager $prodManager
     * @return Product
     */
    public function setProdManager(\Ach\PoManagerBundle\Entity\ProdManager $prodManager = null)
    {
        $this->prodManager = $prodManager;
    
        return $this;
    }

    /**
     * Get prodManager
     *
     * @return \Ach\PoManagerBundle\Entity\ProdManager 
     */
    public function getProdManager()
    {
        return $this->prodManager;
    }

    /**
     * Set shippingManager
     *
     * @param \Ach\PoManagerBundle\Entity\ShippingManager $shippingManager
     * @return Product
     */
    public function setShippingManager(\Ach\PoManagerBundle\Entity\ShippingManager $shippingManager = null)
    {
        $this->shippingManager = $shippingManager;
    
        return $this;
    }

    /**
     * Get shippingManager
     *
     * @return \Ach\PoManagerBundle\Entity\ShippingManager 
     */
    public function getShippingManager()
    {
        return $this->shippingManager;
    }

    /**
     * Set billingManager
     *
     * @param \Ach\PoManagerBundle\Entity\BillingManager $billingManager
     * @return Product
     */
    public function setBillingManager(\Ach\PoManagerBundle\Entity\BillingManager $billingManager = null)
    {
        $this->billingManager = $billingManager;
    
        return $this;
    }

    /**
     * Get billingManager
     *
     * @return \Ach\PoManagerBundle\Entity\BillingManager 
     */
    public function getBillingManager()
    {
        return $this->billingManager;
    }

    /**
     * Set prodName
     *
     * @param string $prodName
     * @return Product
     */
    public function setProdName($prodName)
    {
        $this->prodName = $prodName;
    
        return $this;
    }

    /**
     * Get prodName
     *
     * @return string 
     */
    public function getProdName()
    {
        return $this->prodName;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Product
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
     * Get spareParts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpareParts()
    {
        return $this->spareParts;
    }

    public function getShortDescription()
    {
        return (str_pad($this->pn, 5, '0', STR_PAD_LEFT) . ' - ' . $this->custPn . ' - ' . $this->description);
    }

    /**
     * Set elifesheet
     *
     * @param boolean $elifesheet
     * @return Product
     */
    public function setElifesheet($elifesheet)
    {
        $this->elifesheet = $elifesheet;
    
        return $this;
    }

    /**
     * Get elifesheet
     *
     * @return boolean 
     */
    public function getElifesheet()
    {
        return $this->elifesheet;
    }

    /**
     * Add partOfProducts
     *
     * @param \Ach\PoManagerBundle\Entity\Product $partOfProducts
     * @return Product
     */
    public function addPartOfProduct(\Ach\PoManagerBundle\Entity\Product $partOfProducts)
    {
        $this->partOfProducts[] = $partOfProducts;
    
        return $this;
    }

    /**
     * Remove partOfProducts
     *
     * @param \Ach\PoManagerBundle\Entity\Product $partOfProducts
     */
    public function removePartOfProduct(\Ach\PoManagerBundle\Entity\Product $partOfProducts)
    {
        $this->partOfProducts->removeElement($partOfProducts);
    }

    /**
     * Get partOfProducts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPartOfProducts()
    {
        return $this->partOfProducts;
    }

    /**
     * Add spareParts
     *
     * @param \Ach\PoManagerBundle\Entity\Product $spareParts
     * @return Product
     */
    public function addSparePart(\Ach\PoManagerBundle\Entity\Product $spareParts)
    {
        $this->spareParts[] = $spareParts;
    
        return $this;
    }

    /**
     * Remove spareParts
     *
     * @param \Ach\PoManagerBundle\Entity\Product $spareParts
     */
    public function removeSparePart(\Ach\PoManagerBundle\Entity\Product $spareParts)
    {
        $this->spareParts->removeElement($spareParts);
    }
}