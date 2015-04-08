<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ach\PoManagerBundle\Entity\PoItem;
use Ach\PoManagerBundle\Entity\Invoice;

class PoManagerSearchShipmentItemController extends Controller
{
	
	/* Search by PO number Control */
	public function searchShipmentItemPoNumberAction($poNum, $minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		$repoShipmentItem = $this->getDoctrine()
								->getManager()
								->getRepository('AchPoManagerBundle:ShipmentItem');
		
		$request = $this->getRequest();
		
		$shipmentItems = $repoShipmentItem->findByPoNum($poNum, $filterDate, ($request->query->get('match') == 'exact') );
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by P/N Control */
	public function searchShipmentItemPnAction($pn, $minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		
		$request = $this->getRequest();
		
		$shipmentItems = $repoShipmentItem->findByPn($pn, $filterDate, ($request->query->get('match') == 'exact') );
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by Customer P/N Control */
	public function searchShipmentItemCustPnAction($custPn, $minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		
		$request = $this->getRequest();
		
		$shipmentItems = $repoShipmentItem->findByCustPn($custPn, $filterDate, ($request->query->get('match') == 'exact') );
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by Product Description Control */
	public function searchShipmentItemDescAction($desc, $minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByDescription($desc, $filterDate);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by Shipping Date Control */
	public function searchShipmentItemDateAction($minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByShippingDate($filterDate);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by Tracking Number Control */
	public function searchShipmentItemTrackingAction($tracking, $minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByTracking($tracking, $filterDate);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Search by PoItem using Parameter converter (see router) Control */
	public function searchShipmentItemPoItemAction(PoItem $poItem)
	{
		//echo $poItem->getDescription();
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByPoItem($poItem);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
		// return;
	}
	
	
	/* Search by Invoice using Parameter converter (see router) Control */
	public function searchShipmentItemInvoiceAction(Invoice $invoice)
	{
		//echo $poItem->getDescription();
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByInvoice($invoice);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
		// return;
	}
	
	
	/* Search by Shipping Date Control */
	public function searchShipmentItemInvoiceDateAction($minDate, $maxDate)
	{
		$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
		
		$repoShipmentItem = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repoShipmentItem->findByInvoiceDate($filterDate);
		
		$request = $this->getRequest();
		
		return $this->generateResponse($request, $shipmentItems);
	}
	
	
	/* Generate response depending on the option */
	private function generateResponse($request, $shipmentItems)
	{
		if($request->query->get('return') == 'xls')
		{
			return $this->generateShipmentItemXls($shipmentItems);
		}
		elseif($request->query->get('return') == 'json')
		{
			return $this->generatePoItemJson($shipmentItems);
		}
		else
		{
			return $this->render('AchPoManagerBundle:PoManager:displayListShipmentItem.html.twig', array('shipmentItems' => $shipmentItems));
		}
	}


	/* Generate Excel spreadsheet from PoItems query */
	private function generateShipmentItemXls($shipmentItems)
	{

	// if query has no match return info
	if(empty($shipmentItems))
	{
		$response = new Response;
		$response->setContent('Query has no match');
		$response->setStatusCode(404);
		return $response;
	}

	// store data as a 2-dimensional array with key names
	foreach ($shipmentItems as $key=>$shipmentItem)
	{
		$data[$key][$this->container->getParameter('po_num_header')] = $shipmentItem->getPoItem()->getPo()->getNum();
		$data[$key][$this->container->getParameter('rel_num_header')] = $shipmentItem->getPoItem()->getPo()->getRelNum();
		$data[$key][$this->container->getParameter('line_num_header')] = $shipmentItem->getPoItem()->getLineNum();
		$data[$key][$this->container->getParameter('pn_header')] = $shipmentItem->getPoItem()->getRevision()->getProduct()->getPn();
		$data[$key][$this->container->getParameter('cust_pn_header')] = $shipmentItem->getPoItem()->getRevision()->getProduct()->getCustPn();
		$data[$key][$this->container->getParameter('desc_header')] = $shipmentItem->getPoItem()->getRevision()->getProduct()->getDescription();
		$data[$key][$this->container->getParameter('price_header')] = $shipmentItem->getPoItem()->getPrice()->getPrice();
		$data[$key][$this->container->getParameter('qty_header')] = $shipmentItem->getQty();
		$data[$key][$this->container->getParameter('total_item_header')] = $shipmentItem->getQty() * $shipmentItem->getPoItem()->getPrice()->getPrice();
		$data[$key][$this->container->getParameter('currency_header')] = $shipmentItem->getPoItem()->getPrice()->getCurrency()->getTLA();
		$data[$key][$this->container->getParameter('due_date_header')] = $shipmentItem->getPoItem()->getDueDate()->format("d-M-Y");
		$data[$key][$this->container->getParameter('comment_header')] = $shipmentItem->getPoItem()->getComment();
		$data[$key]['tracking Number'] = $shipmentItem->getShipment()->getTrackingNum();
		$data[$key]['Ship. Depart. Date.'] = $shipmentItem->getShipment()->getShippingDate()->format("d-M-Y");
		if(is_null($shipmentItem->getInvoice()))
			$data[$key]['Invoice Number'] = 'Not invoiced';
		else
			$data[$key]['Invoice Number'] = $shipmentItem->getInvoice()->getNum();
	}

	//call GenerateXlsResponse service and generate Response
	return $this->get('ach_po_manager.generate_xls_response')->generate($data, 'PoManager_Export', 'ShipmentItemList');

	}


	/* Generate Json format from Products query */	  
	private function generateProductJson($products)
	{
	// get the latest rev of the product
	$repository = $this->getDoctrine()
					   ->getManager()
			   ->getRepository('AchPoManagerBundle:Revision');
	$activeRev = $repository->findLatestActiveRev($products[0]->getPn());
	if($activeRev == null)
	{
		$rev = "N/A";
	}
	else
	{
		$rev = $activeRev->getRevisionCust();
	}

	$jsonTable = array("PN" => $products[0]->getPn(), "SKPN" => $products[0]->getCustPn(), "DESC" => $products[0]->getDescription(), "PRICE" => $products[0]->getPrice()->getPrice(), "CURRENCY" => $products[0]->getPrice()->getCurrency()->getTLA(), "REV" => $rev);
	$response = new JsonResponse();
	$response->setData($jsonTable);
	return $response;
	}	


}