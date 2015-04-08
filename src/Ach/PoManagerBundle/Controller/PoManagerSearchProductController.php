<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class PoManagerSearchProductController extends Controller
{

    /* Search by Product P/N Control */
    public function searchProductPnAction($pn)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Product');
	
	$request = $this->getRequest();
	if($request->query->get('match') != 'exact')
	{
	    if($request->query->get('active') != 'true')
	    {
		$products = $repository->findPn($pn);
	    }
	    else
	    {
		$products = $repository->findPnActive($pn);
	    }
	}
	else
	{
	    $products = $repository->findBy(array('pn' => $pn)); // should match exactly the string
	}

	return $this->generateResponse($request, $products);

    }


    /* Search by Product Customer P/N Control */
    public function searchProductCustpnAction($custPn)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Product');

	$request = $this->getRequest();
	if($request->query->get('match') != 'exact')
	{
	    if($request->query->get('active') != 'true')
	    {
	        $products = $repository->findCustPn($custPn);
	    }
	    else
	    {
	        $products = $repository->findCustPnActive($custPn);
	    }
	}
	else
	{
	    $products = $repository->findBy(array('custPn' => $custPn));
	}
	
	return $this->generateResponse($request, $products);
	
    }

    
    /* Search by Product Description Control */
    public function searchProductDescAction($desc)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Product');
	$products = $repository->findDescription($desc);

	$request = $this->getRequest();

	return $this->generateResponse($request, $products);
	
    }


    /* Generate response depending on the option */
    private function generateResponse($request, $products)
    {
	if($request->query->get('return') == 'xls')
	{
	    return $this->generateProductXls($products);
	}
	elseif($request->query->get('return') == 'json')
	{
	    return $this->generateProductJson($products);
	}
	else
	{
	    return $this->render('AchPoManagerBundle:PoManager:displayListProduct.html.twig', array('products' => $products));
	}
    }


    /* Generate Excel spreadsheet from Product query */
    private function generateProductXls($products)
    {

	// if query has no match return info
	if(empty($products))
	{
	    $response = new Response;
	    $response->setContent('Query has no match');
	    $response->setStatusCode(404);
	    return $response;
	}

	// store data as a 2-dimensional array with key names
	foreach ($products as $key=>$product)
	{
	    $data[$key][$this->container->getParameter('pn_header')] = $product->getPn();
	    $data[$key][$this->container->getParameter('cust_pn_header')] = $product->getCustPn();
	    $data[$key][$this->container->getParameter('desc_header')] = $product->getDescription();
	    $data[$key][$this->container->getParameter('price_header')] = $product->getPrice()->getPrice();
	    $data[$key][$this->container->getParameter('currency_header')] = $product->getPrice()->getCurrency()->getTLA();
	    $data[$key][$this->container->getParameter('moq_header')] = $product->getMoq();
	    $data[$key][$this->container->getParameter('comment_header')] = $product->getComment();
	    $data[$key][$this->container->getParameter('prod_manager_header')] = $product->getProdManager()->getEmail();
	    $data[$key][$this->container->getParameter('billing_manager_header')] = $product->getBillingManager()->getEmail();
	}

	//call GenerateXlsResponse service and generate Response
	return $this->get('ach_po_manager.generate_xls_response')->generate($data, 'PoManager_Export', 'ProductList');

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

	$jsonTable = array(
						"PN" => $products[0]->getPn(),
						"SKPN" => $products[0]->getCustPn(),
						"DESC" => $products[0]->getDescription(),
						"PRICE" => $products[0]->getPrice()->getPrice(),
						"CURRENCY" => $products[0]->getPrice()->getCurrency()->getTLA(),
						"REV" => $rev,
						"HISTORY" => $this->getHistoryInfo($products[0]->getPn())
							);
	$response = new JsonResponse();
	$response->setData($jsonTable);
	return $response;
    }	

		private function getHistoryInfo($pn)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');

	//get last poitem that match $pn or null if never ordered
	$poItem = $repository->findLatestPn($pn);
	if($poItem == null)
	{
	    return 'This product was never ordered before or is not recorded in this database';
	}
	else
	{
	    return ('Last order under PO # ' . $poItem->getPo()->getNum() . ' Release # ' . $poItem->getPo()->getRelNum() . ' with due date on ' . $poItem->getDueDate()->format('Y M d'));
	}

    }

}