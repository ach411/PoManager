<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ach\PoManagerBundle\Entity\ShipmentBatch;

class PoManagerProcessShipmentBatchController extends Controller
{

	public function ProcessShipmentBatchShowAction($productName)
	{
		$request = $this->getRequest();
		
		$repositoryLot = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:ShipmentBatch');
		//$lots = $repositoryLot->findByWaitingForRemoval(false);
        $lots = $repositoryLot->findAvailableByProductName($productName);

        $productNameUpper = strtoupper($productName);
        $unitsPerLot = $this->container->getParameter('lot');

		//$this->addReleaseQty($bpos);
		
		return $this->generateResponse($request, $lots, $unitsPerLot[$productNameUpper], $productName);
	}

    public function ProcessShipmentBatchUpdateAction($productName)
    {
        // call service to synchronize with production database
        $log = $this->container->get('ach_po_manager.sync_prod_database')->syncShipmentBatch($productName);

        // redirect to main page
        return $this->redirect($this->generateUrl('ach_po_manager_process_shipmentbatch', array('productName' => $productName) ));
    }

    public function ProcessShipmentBatchSelectAction(ShipmentBatch $lot)
    {
        $em = $this->getDoctrine()->getManager();
        $lot->setWaitingForRemoval(true);
        $em->persist($lot);
        $em->flush();
        return new Response("selected");
    }

    public function ProcessShipmentBatchUnselectAction(ShipmentBatch $lot)
    {
        $em = $this->getDoctrine()->getManager();
        $lot->setWaitingForRemoval(false);
        $em->persist($lot);
        $em->flush();
        return new Response("unselected");
    }

    
    /* Search by PO number Control */
    /* public function searchBpoNumberAction($bpoNum) */
    /* { */
	/* 	$request = $this->getRequest(); */
	
	/* 	$repositoryBpo = $this->getDoctrine() */
	/* 						->getManager() */
	/* 						->getRepository('AchPoManagerBundle:Bpo'); */
		
	/* 	$bpos = $repositoryBpo->FindByBpoNum($bpoNum, ($request->query->get('match') == 'exact') ); */
		
	/* 	$this->addReleaseQty($bpos); */
		
	/* 	return $this->generateResponse($request, $bpos); */
	/* } */



    /* /\* Search by Product P/N Control *\/ */
    /* public function searchBpoPnAction($pn) */
    /* { */
	/* 	$repositoryBpo = $this->getDoctrine() */
	/* 						->getManager() */
	/* 						->getRepository('AchPoManagerBundle:Bpo'); */
		
	/* 	$request = $this->getRequest(); */
		
	/* 	$bpos = $repositoryBpo->findByPn($pn, $request->query->get('match') == 'exact'); */
		
	/* 	$this->addReleaseQty($bpos); */
		
	/* 	return $this->generateResponse($request, $bpos); */
    /* } */


    /* /\* Search by Customer P/N Control *\/ */
    /* public function searchBpoCustPnAction($custPn) */
    /* { */
	/* 	$repositoryBpo = $this->getDoctrine() */
	/* 						->getManager() */
	/* 						->getRepository('AchPoManagerBundle:Bpo'); */
		
	/* 	$request = $this->getRequest(); */
		
	/* 	$bpos = $repositoryBpo->findByCustPn($custPn, $request->query->get('match') == 'exact'); */
		
	/* 	$this->addReleaseQty($bpos); */
		
	/* 	return $this->generateResponse($request, $bpos); */
    /* } */

	/* /\* Search by Customer P/N Control *\/ */
    /* public function searchBpoDescAction($desc) */
    /* { */
	/* 	$repositoryBpo = $this->getDoctrine() */
	/* 						->getManager() */
	/* 						->getRepository('AchPoManagerBundle:Bpo'); */
		
	/* 	$request = $this->getRequest(); */
		
	/* 	$bpos = $repositoryBpo->findByDescription($desc); */
		
	/* 	$this->addReleaseQty($bpos); */
		
	/* 	return $this->generateResponse($request, $bpos); */
    /* } */
    
    /* /\* Search by Product Description Control *\/ */
    /* public function searchPoItemDescAction($desc, $minDate, $maxDate) */
    /* { */
	/* $filterDate = PoManagerControllerUtility::convertDateFilter($minDate, $maxDate); */

	/* $repository = $this->getDoctrine() */
	/*                    ->getManager() */
	/* 		   ->getRepository('AchPoManagerBundle:PoItem'); */
	/* $poItems = $repository->findDescription($desc, $filterDate); */

	/* $request = $this->getRequest(); */
	
	/* $this->addReleaseQty($bpos); */

	/* return $this->generateResponse($request, $poItems); */
	
    /* } */


    /* Generate response depending on the option */
    private function generateResponse($request, $lots, $unitsPerLot, $productName)
    {
	if($request->query->get('return') == 'xls')
	{
	    /* return $this->generateBpoXls($bpos); */
	}
	elseif($request->query->get('return') == 'json')
	{
	    /* return $this->generateBpoJson($bpos); */
	}
	else
	{
	    return $this->render('AchPoManagerBundle:PoManager:displayListShipmentBatch.html.twig', array('lots' => $lots, 'unitsPerLot' => $unitsPerLot, 'productName' => $productName));
	}
    }


    /* Generate Excel spreadsheet from PoItems query */
    /* private function generateBpoXls($bpos) */
    /* { */

	/* // if query has no match return info */
	/* if(empty($bpos)) */
	/* { */
	/*     $response = new Response; */
	/*     $response->setContent('Query has no match'); */
	/*     $response->setStatusCode(404); */
	/*     return $response; */
	/* } */

	/* // store data as a 2-dimensional array with key names */
	/* foreach ($bpos as $key=>$bpoItem) */
	/* { */

	/*     $data[$key][$this->container->getParameter('bpo_num_header')] = $bpoItem->getNum(); */
	/*     $data[$key][$this->container->getParameter('pn_header')] = $bpoItem->getRevision()->getProduct()->getPn(); */
	/*     $data[$key][$this->container->getParameter('cust_pn_header')] = $bpoItem->getRevision()->getProduct()->getCustPn(); */
	/*     $data[$key][$this->container->getParameter('desc_header')] = $bpoItem->getRevision()->getProduct()->getDescription(); */
	/*     $data[$key][$this->container->getParameter('price_header')] = $bpoItem->getPrice()->getPrice(); */
	/* 	$data[$key][$this->container->getParameter('released_qty_header')] = $bpoItem->getReleasedQty(); */
	/* 	$data[$key][$this->container->getParameter('total_qty_header')] = $bpoItem->getQty(); */
	/*     $data[$key][$this->container->getParameter('total_item_header')] = $bpoItem->getQty() * $bpoItem->getPrice()->getPrice(); */
	/*     $data[$key][$this->container->getParameter('currency_header')] = $bpoItem->getPrice()->getCurrency()->getTLA(); */
	/*     $data[$key][$this->container->getParameter('start_date_header')] = (is_null($bpoItem->getStartDate()) ? "" : $bpoItem->getStartDate()->format("d-M-Y")); */
	/*     $data[$key][$this->container->getParameter('end_date_header')] = (is_null($bpoItem->getEndDate()) ? "" : $bpoItem->getEndDate()->format("d-M-Y")); */
	/*     $data[$key][$this->container->getParameter('comment_header')] = $bpoItem->getComment(); */
	/* } */

	/* //call GenerateXlsResponse service and generate Response */
	/* return $this->get('ach_po_manager.generate_xls_response')->generate($data, 'PoManager_Export', 'BpoList'); */

    /* } */


    /* /\* Generate Json format from Products query *\/     */
    /* private function generateProductJson($products) */
    /* { */
	/* // get the latest rev of the product */
	/* $repository = $this->getDoctrine() */
	/*                    ->getManager() */
	/* 		   ->getRepository('AchPoManagerBundle:Revision'); */
	/* $activeRev = $repository->findLatestActiveRev($products[0]->getPn()); */
	/* if($activeRev == null) */
	/* { */
	/* 	$rev = "N/A"; */
	/* } */
	/* else */
	/* { */
	/* 	$rev = $activeRev->getRevisionCust(); */
	/* } */

	/* $jsonTable = array("PN" => $products[0]->getPn(), "SKPN" => $products[0]->getCustPn(), "DESC" => $products[0]->getDescription(), "PRICE" => $products[0]->getPrice()->getPrice(), "CURRENCY" => $products[0]->getPrice()->getCurrency()->getTLA(), "REV" => $rev); */
	/* $response = new JsonResponse(); */
	/* $response->setData($jsonTable); */
	/* return $response; */
    /* }	 */


}
