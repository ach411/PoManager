<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ach\PoManagerBundle\Entity\Notification;

class PoManagerSearchPoItemController extends Controller
{

    /* Search by PO number Control */
    public function searchPoItemNumberAction($poNum, $minDate, $maxDate)
    {
	$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	
	$request = $this->getRequest();

	$poItems = $repository->findPoNum($poNum, $filterDate, ($request->query->get('match') == 'exact') );
	
	return $this->generateResponse($request, $poItems);
    }



    /* Search by Product P/N Control */
    public function searchPoItemPnAction($pn, $minDate, $maxDate)
    {
	$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	
	$request = $this->getRequest();

	$poItems = $repository->findPn($pn, $filterDate, ($request->query->get('match') == 'exact') );

	return $this->generateResponse($request, $poItems);
    }


    /* Search by Product Customer P/N Control */
    public function searchPoItemCustPnAction($custPn, $minDate, $maxDate)
    {
	$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);

	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');

	$request = $this->getRequest();

	$poItems = $repository->findCustPn($custPn, $filterDate, ($request->query->get('match') == 'exact') );
	
	return $this->generateResponse($request, $poItems);
    }

    
    /* Search by Product Description Control */
    public function searchPoItemDescAction($desc, $minDate, $maxDate)
    {
	$filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate);

	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	$poItems = $repository->findDescription($desc, $filterDate);
	
	$request = $this->getRequest();

	return $this->generateResponse($request, $poItems);
	
    }


    /* Generate response depending on the option */
    private function generateResponse($request, $poItems)
    {
	if($request->query->get('return') == 'xls')
	{
	    return $this->generatePoItemXls($poItems);
	}
	elseif($request->query->get('return') == 'json')
	{
	    return $this->generatePoItemJson($poItems);
	}
	else
	{
	    return $this->render('AchPoManagerBundle:PoManager:displayListPoItem.html.twig', array('poItems' => $poItems));
	}
    }


    /* Generate Excel spreadsheet from PoItems query */
    private function generatePoItemXls($poItems)
    {

	// if query has no match return info
	if(empty($poItems))
	{
	    $response = new Response;
	    $response->setContent('Query has no match');
	    $response->setStatusCode(404);
	    return $response;
	}

	// store data as a 2-dimensional array with key names
	foreach ($poItems as $key=>$poItem)
	{

	    $data[$key][$this->container->getParameter('po_num_header')] = $poItem->getPo()->getNum();
	    $data[$key][$this->container->getParameter('rel_num_header')] = $poItem->getPo()->getRelNum();
	    $data[$key][$this->container->getParameter('line_num_header')] = $poItem->getLineNum();
	    $data[$key][$this->container->getParameter('pn_header')] = $poItem->getRevision()->getProduct()->getPn();
	    $data[$key][$this->container->getParameter('cust_pn_header')] = $poItem->getRevision()->getProduct()->getCustPn();
	    $data[$key][$this->container->getParameter('desc_header')] = $poItem->getRevision()->getProduct()->getDescription();
	    $data[$key][$this->container->getParameter('price_header')] = $poItem->getPrice()->getPrice();
	    $data[$key][$this->container->getParameter('qty_header')] = $poItem->getQty();
	    $data[$key][$this->container->getParameter('total_item_header')] = $poItem->getQty() * $poItem->getPrice()->getPrice();
	    $data[$key][$this->container->getParameter('currency_header')] = $poItem->getPrice()->getCurrency()->getTLA();
	    $data[$key][$this->container->getParameter('due_date_header')] = $poItem->getDueDate()->format("d-M-Y");
	}

	//call GenerateXlsResponse service and generate Response
	return $this->get('ach_po_manager.generate_xls_response')->generate($data, 'PoManager_Export', 'PoItemList');

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