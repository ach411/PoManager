<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ShipmentItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShipmentItemRepository extends EntityRepository
{

	const SELECT_ALL = 'SELECT s FROM AchPoManagerBundle:ShipmentItem s ';
	const JOIN_POITEM_REV_PRODUCT = 'JOIN s.poItem i JOIN i.revision r JOIN r.product p ';
	const JOIN_POITEM_PO_SHIPMENT = 'JOIN s.poItem i JOIN i.po o JOIN s.shipment h ';
	const JOIN_POITEM_REV_PRODUCT_SHIPMENT = 'JOIN s.poItem i JOIN i.revision r JOIN r.product p JOIN s.shipment h ';
	const JOIN_SHIPMENT = 'JOIN s.shipment h ';
	const JOIN_INVOICE = 'JOIN s.invoice v ';
	
	const DATE_FILTER_ORDER = 'h.shippingDate >= :earliest AND h.shippingDate <= :latest ORDER BY h.shippingDate DESC';
	const INVOICE_DATE_FILTER_ORDER = 'v.invoiceDate >= :earliest AND v.invoiceDate <= :latest ORDER BY v.invoiceDate DESC';
	
	
	private function setParameterWithFilter($paramKey, $paramValue, $query, $filterDate, $exact = false)
	{
		if($exact)
		{
			$query->setParameter($paramKey, $paramValue);
		}
		else
		{
			$query->setParameter($paramKey, '%' . $paramValue . '%');
		}
		
		$query->setParameter('earliest', $filterDate['earliest']);
		$query->setParameter('latest', $filterDate['latest']);
	}

	public function findByBillingManager($billingManagerId)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_REV_PRODUCT . 'JOIN p.billingManager b WHERE b.id = :billingManagerId ');
		$query->setParameter('billingManagerId', $billingManagerId);
		return $query->getResult();
	}

	public function findNotInvoicedByBillingManager($billingManagerId)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_REV_PRODUCT . 'JOIN p.billingManager b WHERE b.id = :billingManagerId AND s.invoice is null');
		$query->setParameter('billingManagerId', $billingManagerId);
		return $query->getResult();
	}
	
	public function findByPoNum($poNum, $filterDate, $exact)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_PO_SHIPMENT . ' WHERE o.num like :num AND ' . self::DATE_FILTER_ORDER);
		
		$this->setParameterWithFilter('num', $poNum, $query, $filterDate, $exact);
		
		return $query->getResult();
	}
	
	public function findByPn($pn, $filterDate, $exact)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_REV_PRODUCT_SHIPMENT . 'WHERE p.pn like :pn AND ' . self::DATE_FILTER_ORDER);
		
		$this->setParameterWithFilter('pn', $pn, $query, $filterDate, $exact);
		
		return $query->getResult();
	}
	
	public function findByCustPn($custPn, $filterDate, $exact)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_REV_PRODUCT_SHIPMENT . 'WHERE p.custPn like :custPn AND ' . self::DATE_FILTER_ORDER);
		
		$this->setParameterWithFilter('custPn', $custPn, $query, $filterDate, $exact);
		
		return $query->getResult();
	}
	
	public function findByDescription($desc, $filterDate)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_POITEM_REV_PRODUCT_SHIPMENT . 'WHERE p.description like :desc AND ' . self::DATE_FILTER_ORDER);
		
		$this->setParameterWithFilter('desc', $desc, $query, $filterDate);
		
		return $query->getResult();
	}
	
	public function findByShippingDate($filterDate)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_SHIPMENT . 'WHERE ' . self::DATE_FILTER_ORDER);
		
		$query->setParameter('earliest', $filterDate['earliest']);
		$query->setParameter('latest', $filterDate['latest']);
		
		return $query->getResult();
	}
	
	public function findByTracking($tracking, $filterDate)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_SHIPMENT . 'WHERE h.trackingNum like :tracking AND ' . self::DATE_FILTER_ORDER);
		
		$this->setParameterWithFilter('tracking', $tracking, $query, $filterDate);
		
		return $query->getResult();
	}
	
	public function findByInvoiceDate($filterDate)
	{
		$query = $this->_em->createQuery(self::SELECT_ALL . self::JOIN_INVOICE . 'WHERE ' . self::INVOICE_DATE_FILTER_ORDER);
		
		$query->setParameter('earliest', $filterDate['earliest']);
		$query->setParameter('latest', $filterDate['latest']);
		
		return $query->getResult();
	}
}
