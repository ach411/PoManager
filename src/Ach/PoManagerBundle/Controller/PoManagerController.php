<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ach\PoManagerBundle\Entity\Product;
use Ach\PoManagerBundle\Entity\Po;
use Ach\PoManagerBundle\Entity\Status;
use Ach\PoManagerBundle\Entity\HistoryStatus;
use Ach\PoManagerBundle\Entity\PendingNotification;
use Ach\PoManagerBundle\Entity\PoItem;
use Ach\PoManagerBundle\Entity\Price;
use Ach\PoManagerBundle\Entity\Shipment;
use Ach\PoManagerBundle\Entity\Invoice;
use Ach\PoManagerBundle\Entity\Bpo;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;

use Ach\PoManagerBundle\Form\ProductSearchPnType;
use Ach\PoManagerBundle\Form\ProductSearchCustPnType;
use Ach\PoManagerBundle\Form\ProductSearchDescType;
use Ach\PoManagerBundle\Form\ParsePoType;
use Ach\PoManagerBundle\Form\PoType;
use Ach\PoManagerBundle\Form\PoItemSearchNumberType;
use Ach\PoManagerBundle\Form\PoItemSearchPnType;
use Ach\PoManagerBundle\Form\PoItemSearchCustPnType;
use Ach\PoManagerBundle\Form\PoItemSearchDescType;
use Ach\PoManagerBundle\Form\ShipmentItemSearchTrackingNumberType;
use Ach\PoManagerBundle\Form\ShipmentItemSearchShippingDateType;
use Ach\PoManagerBundle\Form\ShipmentItemSearchInvoiceType;
use Ach\PoManagerBundle\Form\BpoSearchNumberType;
use Ach\PoManagerBundle\Form\BpoSearchPnType;
use Ach\PoManagerBundle\Form\BpoSearchCustPnType;
use Ach\PoManagerBundle\Form\BpoSearchDescType;
use Ach\PoManagerBundle\Form\ParseShipmentBatchType;
use Ach\PoManagerBundle\Form\SerialNumberSearchType;
use Ach\PoManagerBundle\Form\SerialNumberSearchMacAddressType;

class PoManagerController extends Controller
{
    public function indexAction()
    {
	$product = new Product();
	$po = new Po();
	$shipment = new Shipment();
	$invoice = new Invoice();
	$bpo = new Bpo();
    $shipmentBatch = new ShipmentBatch();
    $sn = new SerialNumber();
	
	// create forms for the product search
	$formPn = $this->createForm(new ProductSearchPnType, $product);
	$formCustPn = $this->createForm(new ProductSearchCustPnType, $product);
	$formDesc = $this->createForm(new ProductSearchDescType, $product);
    
	// create form for the file parsing to create PO
	$formParsePo = $this->createForm(new ParsePoType, $po);

    // create form for the file parsing to create ShipmentBatch
    $formParseShipmentBatch = $this->createForm(new ParseShipmentBatchType, $shipmentBatch);

	// create forms for the Po Item search
	$formPoItemPn = $this->createForm(new PoItemSearchPnType, $product);
	$formPoItemCustPn = $this->createForm(new PoItemSearchCustPnType, $product);
	$formPoItemDesc = $this->createForm(new PoItemSearchDescType, $product);
	$formPoItemNum = $this->createForm(new PoItemSearchNumberType, $po);
	
	// create forms for the Shipment Item search (not really useful except search by tracking number and date)
		// $formShipmentItemPn = $this->createForm(new ShipmentItemSearchPnType, $product);
		// $formShipmentItemCustPn = $this->createForm(new ShipmentItemSearchCustPnType, $product);
		// $formShipmentItemDesc = $this->createForm(new ShipmentItemSearchDescType, $product);
		// $formShipmentItemNum = $this->createForm(new ShipmentItemSearchNumberType, $po);
	$formShipmentItemTrackingNum = $this->createForm(new ShipmentItemSearchTrackingNumberType, $shipment);
	$formShipmentItemShippingDate = $this->createForm(new ShipmentItemSearchShippingDateType, $shipment);
	$formShipmentItemInvoice = $this->createForm(new ShipmentItemSearchInvoiceType, $invoice);

    // create form for search by serial number
    $formSerialNumber = $this->createForm(new SerialNumberSearchType, $sn);
    $formSerialNumberMac = $this->createForm(new SerialNumberSearchMacAddressType, $sn);
    
    // create forms for the BPO search
	$formBpoPn = $this->createForm(new BpoSearchPnType, $product);
	$formBpoCustPn = $this->createForm(new BpoSearchCustPnType, $product);
	$formBpoDesc = $this->createForm(new BpoSearchDescType, $product);
	$formBpoNum = $this->createForm(new BpoSearchNumberType, $bpo);
	
	$request = $this->get('request');
	if ($request->getMethod() == 'POST')
	{
	    $formPn->bind($request);
	    if($formPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_product_pn', array('pn' => $product->getPn())) );
	    }
	    
	    $formCustPn->bind($request);
	    if($formCustPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_product_custpn', array('custPn' => $product->getCustPn())) );
	    }

	    $formDesc->bind($request);
	    if($formDesc->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_product_desc', array('desc' => $product->getDescription())) );
	    }

	    $formPoItemNum->bind($request);
	    if($formPoItemNum->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_poitem_number', array('poNum' => $po->getNum())) );
	    }

	    $formPoItemPn->bind($request);
	    if($formPoItemPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_poitem_pn', array('pn' => $product->getPn())) );
	    }

	    $formPoItemCustPn->bind($request);
	    if($formPoItemCustPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_poitem_custpn', array('custPn' => $product->getCustPn())) );
	    }

	    $formPoItemDesc->bind($request);
	    if($formPoItemDesc->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_poitem_desc', array('desc' => $product->getDescription())) );
	    }

		/*
	    $formShipmentItemNum->bind($request);
	    if($formShipmentItemNum->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_ponumber', array('poNum' => $po->getNum())) );
	    }

	    $formShipmentItemPn->bind($request);
	    if($formShipmentItemPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_pn', array('pn' => $product->getPn())) );
	    }

	    $formShipmentItemCustPn->bind($request);
	    if($formShipmentItemCustPn->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_custpn', array('custPn' => $product->getCustPn())) );
	    }

	    $formShipmentItemDesc->bind($request);
	    if($formShipmentItemDesc->isValid())
	    {
		return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_desc', array('desc' => $product->getDescription())) );
	    }
		*/
		
		$formShipmentItemTrackingNum->bind($request);
		if($formShipmentItemTrackingNum->isValid())
		{
			return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_tracking', array('tracking' => $shipment->getTrackingNum())) );
		}
		
		$formShipmentItemShippingDate->bind($request);
		if($formShipmentItemShippingDate->isValid())
		{
			return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_date', array('minDate' => $shipment->getShippingDateF())) );
		}
		
		$formShipmentItemInvoice->bind($request);
		if($formShipmentItemInvoice->isValid())
		{
			return $this->redirect($this->generateUrl('ach_po_manager_search_shipmentitem_invoicenum', array('num' => $invoice->getNum())) );
		}

        $formSerialNumber->bind($request);
        if($formSerialNumber->isValid())
        {
            return $this->redirect($this->generateUrl('ach_po_manager_search_serial_number', array('sn' => $sn->getSerialNumber())) );
        }

        $formSerialNumberMac->bind($request);
        if($formSerialNumberMac->isValid())
        {
            return $this->redirect($this->generateUrl('ach_po_manager_search_serial_number_mac_address', array('mac' => $sn->getMacAddress())) );
        }

		$formBpoNum->bind($request);
	    if($formBpoNum->isValid())
	    {
			return $this->redirect($this->generateUrl('ach_po_manager_search_bpo_ponum', array('bpoNum' => $bpo->getNum())) );
	    }

	    $formBpoPn->bind($request);
	    if($formBpoPn->isValid())
	    {
			return $this->redirect($this->generateUrl('ach_po_manager_search_bpo_pn_num', array('pn' => $product->getPn())) );
	    }

	    $formBpoCustPn->bind($request);
	    if($formBpoCustPn->isValid())
	    {
			return $this->redirect($this->generateUrl('ach_po_manager_search_bpo_custpn', array('custPn' => $product->getCustPn())) );
	    }

	    $formBpoDesc->bind($request);
	    if($formBpoDesc->isValid())
	    {
			return $this->redirect($this->generateUrl('ach_po_manager_search_bpo_desc', array('desc' => $product->getDescription())) );
	    }
		
	}
	
	return $this->render('AchPoManagerBundle:PoManager:index.html.twig', array(
	       'formPn' => $formPn->createView(),
	       'formCustPn' => $formCustPn->createView(),
	       'formDesc' => $formDesc->createView(),
	       'formParsePo' => $formParsePo->createView(),
           'formParseShipmentBatch' => $formParseShipmentBatch->createView(),
	       'formPoItemNum' => $formPoItemNum->createView(),
	       'formPoItemPn' => $formPoItemPn->createView(),
	       'formPoItemCustPn' => $formPoItemCustPn->createView(),
	       'formPoItemDesc' => $formPoItemDesc->createView(),
		   'formShipmentItemTrackingNum' => $formShipmentItemTrackingNum->createView(),
		   'formShipmentItemShippingDate' => $formShipmentItemShippingDate->createView(),
		   'formShipmentItemInvoice' => $formShipmentItemInvoice->createView(),
           'formSerialNumber' => $formSerialNumber->createView(),
           'formSerialNumberMac' => $formSerialNumberMac->createView(),
		   'formBpoNum' => $formBpoNum->createView(),
		   'formBpoPn' => $formBpoPn->createView(),
		   'formBpoCustPn' => $formBpoCustPn->createView(),
		   'formBpoDesc' => $formBpoDesc->createView()
	       ));
    }


    public function sendNotificationAction()
    {

	$repositoryNotification = $this->getDoctrine()
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Notification');

	$listNotifications = $repositoryNotification->findAll();

	$em = $this->getDoctrine()->getManager();
	
	$log = null;
	//$log = getcwd();

	// scan all the pending notification of the table and send message for each
	foreach($listNotifications as $notification)
	{
	    $log = $log.$this->get('ach_po_manager.send_notification')->sendNotification($notification);
	    $em->remove($notification);
	}

	$em->flush();

	// return new Response("Notifications sent: ".$log);	       
	//return new Response("Notifications sent \n");	       
	// return $this->render('AchPoManagerBundle:PoManager:successNotificationSent.html.twig', array('message' => 'Notification sent: ' . $log));
	return $this->render('AchPoManagerBundle:PoManager:successNotificationSent.html.twig', array('message' => 'Notification sent: '));
    }

    public function killPoAction()
    {
	$repository = $this->getDoctrine()
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Po');
	$po = $repository->find(7);
	
	$em = $this->getDoctrine()->getManager();

	$em->remove($po);

	$em->flush();

	return new Response("Done");
	
    }

    public function approvePoItemAction($poItemId)
    {

	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:PoItem');
	$poItem = $repository->find($poItemId);

	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Status');

	$pendingStatus = $repository->find(2);

	$poItem->setStatus($pendingStatus);

	// create entry in HistoryStatus table
	$historyStatus = new HistoryStatus();
	$historyStatus->setPoItem($poItem);
	$historyStatus->setStatus($pendingStatus);

	// create a notification
	$pendingNotification = new PendingNotification();
	$pendingNotification->setHistoryStatus($historyStatus);

	$em = $this->getDoctrine()->getManager();

	$em->persist($pendingNotification);

	$em->flush();

	return new Response("Po id ".$poItemId." pending");

    }

	public function convertBpoAction()
	{
		$myfile = fopen("/home/vitec/www/bpo_files/bpo.xml", "r");
		$file_string = "";
		$price_id = 0;
		$real_price = 0;
		while(!feof($myfile)) {
			$file_line = fgets($myfile);// . "<br>";
			preg_match("/<column name=\"vitec_index\">(\d+)/", $file_line, $result_array);
			if(empty($result_array) != true)
			{
				$pn = $result_array[1];
				$repository = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Revision');
				$revision = $repository->findByPnUnknownRevision($pn);
				$revision_id = $revision->getId();
				$price_id = $revision->getProduct()->getPrice()->getId();
				$real_price = $revision->getProduct()->getPrice()->getPrice();
				$file_line = preg_replace("/<column name=\"vitec_index\">\d+/", "<column name=\"revision_id\">" . $revision_id, $file_line);
			}
			else
			{
				$file_line = preg_replace("/<column name=\"price_index\">\d+/", "<column name=\"price_id\">" . $price_id, $file_line);
			}
			$file_string .= $file_line;
		}
		fclose($myfile);
		
		$file_string = str_replace('bpo_files/','',$file_string);
		$file_string = str_replace('"bpo_num"','"num"',$file_string);
		$file_string = str_replace('"effective_end_date"','"endDate"',$file_string);
		$file_string = str_replace('"total_qty"','"qty"',$file_string);
		$file_string = str_replace('"pdf_path"','"filePath"',$file_string);
		$file_string = str_replace('"comments"','"comment"',$file_string);
		
		$response = new Response($file_string);
		$response->headers->set('Content-Type', 'text/xml');
		
		return $response;
	}

    public function testxlsAction($index)
    {
        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("ACH")
            ->setLastModifiedBy("ACH")
	    ->setTitle("Un test");
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'UN')
            ->setCellValue('B1', 'test')
	    ->setCellValue('C1', 'avec')
	    ->setCellValue('D1', 'la')
	    ->setCellValue('E1', 'valeur')
	    ->setCellValue('F1', $index);

        $phpExcelObject->getActiveSheet()->setTitle('test');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=product.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;        
    }

    public function dumpSparePartsAction($pn)
    {
        $repositoryProduct = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Product');

        $request = $this->get('request');
        
        if($request->query->get('return') == 'xls')
        {
            $results = $repositoryProduct->findMasterProducts();
            $txt = '';
            foreach($results as $result)
            {
                $txt .= $result->getDescription() . "\n";
            }
            return new Response($txt);
        }

       
        $productInstance = $repositoryProduct->findOneByPn($pn);

        return $this->render('AchPoManagerBundle:PoManager:displayListSpareParts.html.twig', array('product' => $productInstance));
        
        /* $response = new Response('part: '. $productInstance->getDescription()); */
        /* return $response; */
    }

    public function updateProductAction(Product $product)
    {
		$request = $this->get('request');
		$comment = $request->request->get("comment");
		
		$product->setComment($comment);
		$em = $this->getDoctrine()->getManager();
		$em->flush();
		
		return new Response('Comment updated: ' . $comment);
	}
    
}
