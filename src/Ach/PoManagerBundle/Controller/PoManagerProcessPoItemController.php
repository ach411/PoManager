<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Ach\PoManagerBundle\Entity\Status;
use Ach\PoManagerBundle\Entity\Notification;
use Ach\PoManagerBundle\Entity\Shipment;
use Ach\PoManagerBundle\Entity\ShipmentItem;
use Ach\PoManagerBundle\Entity\PoItem;
use Ach\PoManagerBundle\Entity\UploadElifesheetPending;

class PoManagerProcessPoItemController extends Controller
{

    /* Manage the PO Item with the product manager */
    public function processPoItemProdAction($prodManagerId)
    {
	// get all the Po items in review status that depends on specified coordinator
	$repository = $this->getDoctrine()
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:PoItem');
	$poItems = $repository->findPoItemByProdManager($prodManagerId, "IN REVIEW");

	$repository = $this->getDoctrine()
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:ProdManager');
	$prodManager = $repository->find($prodManagerId);

	return $this->render('AchPoManagerBundle:PoManager:processPoItemProd.html.twig', array('poItems' => $poItems, 'coordinator' => $prodManager));

    }


    /* Approve the PoItem: goes from status "IN REVIEW" to "APPROVED"*/
    public function processPoItemApproveAction($poItemId)
    {

	$repositoryPoItem = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	$poItem = $repositoryPoItem->find($poItemId);
	
	// change Approved flag and update status
	$poItem->setApproved(true);
	$repositoryStatus = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Status');
	$poItem->setStatus($repositoryStatus->findOneByName("APPROVED"));
	/*
	// create entry in HistoryStatus table
	$historyStatus = new HistoryStatus();
	$historyStatus->setPoItem($poItem);
	$historyStatus->setStatus($pendingStatus);
	*/

	// create a notification
	// create a new notification with category "APPROVED ORDER NOTIFICATION"
	$teamNotification = $this->get('ach_po_manager.notification_creator')->createNotification($poItem, "APPROVED ORDER NOTIFICATION");
	/*$repositoryNotificationCategory = $this->getDoctrine()
		->getManager()
		->getRepository('AchPoManagerBundle:NotificationCategory');
	$teamNotification = new Notification($poItem, $repositoryNotificationCategory->findOneByName("APPROVED ORDER NOTIFICATION"));*/
	
	// create a notification
	// create a new notification with category "CONFIRM ORDER NOTIFICATION"
	$custNotification = $this->get('ach_po_manager.notification_creator')->createNotification($poItem, "CONFIRM ORDER NOTIFICATION");
	/*$repositoryNotificationCategory = $this->getDoctrine()
		->getManager()
		->getRepository('AchPoManagerBundle:NotificationCategory');
	$custNotification = new Notification($poItem, $repositoryNotificationCategory->findOneByName("CONFIRM ORDER NOTIFICATION"));*/

	$em = $this->getDoctrine()->getManager();

	$em->persist($teamNotification);
	$em->persist($custNotification);
	$em->persist($poItem);

	$em->flush();

	return new Response("Po id ".$poItemId." approved and is now pending");

    }

	/* Reject the PoItem: goes from status "IN REVIEW" to "REJECTED" */
    public function processPoItemRejectAction($poItemId)
    {

	$repositoryPoItem = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	$poItem = $repositoryPoItem->find($poItemId);

	// update status
	$repositoryStatus = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Status');
	$poItem->setStatus($repositoryStatus->findOneByName("REJECTED"));
	
	// add the reason of rejection in the comment field
	$request = $this->get('request');
	$comment = $poItem->getComment();
	$comment .= " REJECTED: " . $request->query->get('info');
	$poItem->setComment($comment);

	
	// create a notification
	// create a new notification with category "REJECTION NOTIFICATION"
	$notification = $this->get('ach_po_manager.notification_creator')->createNotification($poItem, "REJECTION NOTIFICATION");
	/*$repositoryNotificationCategory = $this->getDoctrine()
		->getManager()
		->getRepository('AchPoManagerBundle:NotificationCategory');
	$notification = new Notification($poItem, $repositoryNotificationCategory->findOneByName("REJECTION NOTIFICATION"));*/
	
	$em = $this->getDoctrine()->getManager();

	$em->persist($poItem);
	$em->persist($notification);

	$em->flush();

	return new Response("Po id ".$poItemId." rejected: " . $comment);

    }

    /* Manage the PO Item with the shipping manager */
    public function processPoItemShipAction($shippingManagerId)
    {
	// get all the Po items in approved status that depends on specified ShippingManager
	$repositoryPoItem = $this->getDoctrine()
		        ->getManager()
	    		->getRepository('AchPoManagerBundle:PoItem');
	// $poItemsApproved = $repositoryPoItem->findPoItemByStatusShippingManager($shippingManagerId, "APPROVED");
	// $poItemsPartiallyShipped = $repositoryPoItem->findPoItemByStatusShippingManager($shippingManagerId, "PARTIALLY SHIPPED");

    $poItems = $repositoryPoItem->findPoItemByStatusShippingManager($shippingManagerId, "APPROVED", "PARTIALLY SHIPPED");
	
	// $poItems = array_merge($poItemsApproved, $poItemsPartiallyShipped);
	
	$repositoryShippingManager = $this->getDoctrine()
		        ->getManager()
			->getRepository('AchPoManagerBundle:ShippingManager');
	$shippingManager = $repositoryShippingManager->find($shippingManagerId);

	// get the list of possible carrier
	$repositoryCarrier = $this->getDoctrine()
		        ->getManager()
	    		->getRepository('AchPoManagerBundle:Carrier');
	$carriers = $repositoryCarrier->findAll();
	
	return $this->render('AchPoManagerBundle:PoManager:processPoItemShip.html.twig', array('poItems' => $poItems, 'salesAdmin' => $shippingManager, 'carriers' => $carriers));

    }


    /* Generate an Excel spreadsheet that recap the PO Item selected in ship process */
    public function processPoItemGenerateXlsShipRecapAction()
    {
	// get the poItem id needed
	$request = $this->get('request');
	// $parameters = $request->query->all();
	$parametersKeys = $request->query->keys();

	$repository = $this->getDoctrine()
		        ->getManager()
	    		->getRepository('AchPoManagerBundle:PoItem');

	// foreach($parameters as $parameter)
	foreach($parametersKeys as $parameterKey)
	{
	    if(strpos($parameterKey, 'excel_recap_') !== FALSE )
	    {
		$parameter = $request->query->get($parameterKey);
		$poItems[$parameter] = $repository->find($parameter);
	    }
	}

	$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

	$phpExcelObject->getProperties()->setCreator("ACH")
            ->setLastModifiedBy("ACH")
	    ->setTitle("Recap Novatech");

	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('A1', 'Index');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B1', 'Ref. NH');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('C1', 'Designation');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('D1', 'Test?');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('E1', 'Demande');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('F1', 'Livre');
	$phpExcelObject->setActiveSheetIndex(0)->setCellValue('G1', 'No Identification');
	$phpExcelObject->setActiveSheetIndex(0)->mergeCells('G1:P1');

	$index = 2;

	foreach($poItems as $key => $poItem)
	{
	    // 5 lines for each item
	    $index_end = $index + 5;

	    $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A'.$index, $poItem->getRevision()->getProduct()->getPn() );
	    $phpExcelObject->setActiveSheetIndex(0)->setCellValue('C'.$index, $poItem->getDescription() );
	    //$phpExcelObject->setActiveSheetIndex(0)->setCellValue('E'.$index, $poItem->getQty() );
			$phpExcelObject->setActiveSheetIndex(0)->setCellValue('E'.$index, $request->query->get('qty_id_'.$key) );
	    $phpExcelObject->setActiveSheetIndex(0)->setCellValue('F'.$index, '=COUNTA(G' . $index . ':P' . $index_end . ')');
	    
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A' . $index . ':A' . $index_end);
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('B' . $index . ':B' . $index_end);
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('C' . $index . ':C' . $index_end);
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('D' . $index . ':D' . $index_end);
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('E' . $index . ':E' . $index_end);
	    $phpExcelObject->setActiveSheetIndex(0)->mergeCells('F' . $index . ':F' . $index_end);
	    
	    $index = $index_end + 1;
	}

	$styleArray = array(
	    'borders' => array(
	        'allborders' => array(
		    'style' => \PHPExcel_Style_Border::BORDER_THIN
          	)
	    ),
	    'font' => array(
	        'name'	=> 'Arial',
		'size' => 8
	    )
	);
	$phpExcelObject->getActiveSheet()->getStyle('A1:P' . $index_end)->applyFromArray($styleArray);
	$phpExcelObject->getActiveSheet()->getStyle('C1:C' . $index_end)->getFont()->setBold(true);
	$phpExcelObject->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
	$phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$phpExcelObject->getActiveSheet()->getStyle('A1:P1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$phpExcelObject->getActiveSheet()->getStyle('A2:F' . $index_end)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$phpExcelObject->getActiveSheet()->getStyle('A2:F' . $index_end)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$phpExcelObject->getActiveSheet()->getStyle('A2:F' . $index_end)->getAlignment()->setWrapText(true);

	$phpExcelObject->getActiveSheet()->setTitle('Tab Title');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        // $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . 'Recap Novatech' . '.xlsx');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;

	
    }

	/* Ship the PoItem: goes from status "APPROVED" or "PARTIALLY SHIPPED" to "PARTIALLY SHIPPED" or "SHIPPED"*/
	public function processPoItemTrackingAction($shippingDate, $carrier, $tracking)
	{
		//echo 'tracking number: ' . $tracking;
		
		//test if $tracking value is different than "none"
		if($tracking != "none")
		{
			// get instance of shipment with same tracking if already exists
			$repositoryShipment = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:Shipment');
			$shipment = $repositoryShipment->findOneByTrackingNum($tracking);
		}
		
		// get instance of carrier
		$repositoryCarrier = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:Carrier');
		$carrier = $repositoryCarrier->find($carrier);
		
		// if no shipment entry has this tracking number yet, then create one shipment entry
		if(empty($shipment))
		{
			//echo ' - create new shipment entry - ';
			$shipment = new Shipment($tracking, new \DateTime($shippingDate), $carrier);
		}
		
		// get the poItem id needed
		$request = $this->get('request');
		// $parameters = $request->query->all();
		$parametersKeys = $request->query->keys();

		$repositoryPoItem = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:PoItem');
		
		// foreach($parameters as $parameter)
		foreach($parametersKeys as $parameterKey)
		{
			if(strpos($parameterKey, 'poItem_') !== FALSE )
			{
				$parameter = $request->query->get($parameterKey);
				//echo 'poItem: ' . $parameter . ' - ';
				$poItem = $repositoryPoItem->find($parameter);
				if(empty($poItem))
				{
					return new Response("Error: invalid PO item with id: " . $parameter);
				}
				$poItems[$parameter] = $poItem;
			}
		}
		
		if(empty($poItems))
		{
			return new Response("No Po item found ");
		}
		
		$em = $this->getDoctrine()->getManager();
		
		$notificationRequired = false;
		
		foreach($poItems as $parameter => $poItem)
		{
			//get the qty shipped from the GET request
			$shippedQty = $request->query->get("qty_id_" . $parameter);
			
			// for each quantity of item shipped, create entry in ShipmentItem table
			$shipmentItem = new ShipmentItem($shipment, $poItem, $shippedQty);
			
			//echo $shippedQty . " - ";
			
			// add this shipment qty to already shipped qty
			// $shippedQty += $poItem->getShippedQty();
            $shippedAllQty = $poItem->getShippedQty() + $shippedQty;
			
			//echo $shippedQty . " - ";
			
			// take action accordingly to this new shipment qty
			if($shippedAllQty > $poItem->getQty())
			{
				return new Response("Error: shipping more units than contained in the PO item");
			}
			else
			{
				// update shippedQty value
				$poItem->setShippedQty($shippedAllQty);
				
				if($shippedAllQty == $poItem->getQty())
				{
					//update status of PoItem
					$repositoryStatus = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Status');
					$poItem->setStatus($repositoryStatus->findOneByName("SHIPPED"));
				}
				else
				{
					//update status of PoItem
					$repositoryStatus = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Status');
					$poItem->setStatus($repositoryStatus->findOneByName("PARTIALLY SHIPPED"));
				}
			}
			
			if($poItem->getRevision()->getProduct()->getShippingManager()->getId() != 2)
				$notificationRequired = true;
			
			// create entry in HistoryStatus table
			/*$historyStatus = new HistoryStatus();
			$historyStatus->setPoItem($poItem);
			$historyStatus->setStatus($pendingStatus);
			*/
			// create a notification
			/*$pendingNotification = new PendingNotification();
			$pendingNotification->setHistoryStatus($historyStatus);
			*/

            // manage lots (ShipmentBatch)
            $prodName =  $poItem->getRevision()->getProduct()->getProdName();
            if($prodName != null)
            {
                $repositoryShipmentBatch = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:ShipmentBatch');
                
                $shipmentBatchInstances = $repositoryShipmentBatch->findWaitingForRemovalByProductName($prodName);
		$WaitingForRemovalCount = 0;
                if(empty($shipmentBatchInstances))
                    return new Response("Error: No lot is currently selected for shipment, please select lot(s) for removal first");
		foreach($shipmentBatchInstances as $shipmentBatchInstance)
		{
		    $WaitingForRemovalCount += $shipmentBatchInstance->getSerialNumbers()->count();
		}
                if($WaitingForRemovalCount != $shippedQty)
                    return new Response("Error: the $WaitingForRemovalCount $prodName (waiting for removal) previously selected for that shipment do not match with the entered quantity of $shippedQty!");
                foreach($shipmentBatchInstances as $shipmentBatchInstance)
                {
                    //$shipmentBatchInstance->setShipment($shipment);
                    $shipmentItem->addShipmentBatch($shipmentBatchInstance);
                }
            }

            // add entry in UploadElifesheetPending table for later upload by background task of all the elifesheet of the units within the shipmentItem
            if($poItem->getRevision()->getProduct()->getElifesheet())
            {
                $uploadElifesheetPendingInstance = new UploadElifesheetPending();
                $uploadElifesheetPendingInstance->setShipmentItem($shipmentItem);
                $em->persist($uploadElifesheetPendingInstance);
            }
			
			//persist shipmentItem
			//$em->persist($poItem);
			$em->persist($shipmentItem);
			//$em->persist($pendingNotification);
			
		}
		
		// create a notification if required
		// create a new notification with category "SHIPMENT NOTIFICATION"
		if($notificationRequired)
		{
			$notification = $this->get('ach_po_manager.notification_creator')->createNotification($shipment, "SHIPMENT NOTIFICATION");
			
			/*$repositoryNotificationCategory = $this->getDoctrine()
				->getManager()
				->getRepository('AchPoManagerBundle:NotificationCategory');
			$notification = new Notification($shipment, $repositoryNotificationCategory->findOneByName("SHIPMENT NOTIFICATION"));*/
		
			$em->persist($notification);
		}
		
		$em->persist($shipment);
		$em->flush();
		return new Response('Selected items now have tracking number entry: ' . $tracking);
		
	}

	/* Manage the PO Item with the billing manager */
	public function processPoItemBillAction($billingManagerId)
	{
		// get all the Po items in pending status that depends on specified billingManager
		$repositoryShipmentItem = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:ShipmentItem');
		$shipmentItems = $repositoryShipmentItem->findNotInvoicedByBillingManager($billingManagerId);
	
		$repositoryBillingManager = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:BillingManager');
		$billingManager = $repositoryBillingManager->find($billingManagerId);

		return $this->render('AchPoManagerBundle:PoManager:processPoItemBill.html.twig', array('shipmentItems' => $shipmentItems, 'billingManager' => $billingManager));
	}

	/* Update the comment for PoItem*/
	public function processPoItemUpdateCommentAction(PoItem $poItem)
	{
		$request = $this->get('request');
		$comment = $request->request->get("comment");
		
		$poItem->setComment($comment);
		$em = $this->getDoctrine()->getManager();
		$em->flush();
		
		return new Response('Comment updated: ' . $comment);
	}
}
