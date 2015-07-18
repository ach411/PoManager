<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Ach\PoManagerBundle\Entity\Product;
use Ach\PoManagerBundle\Entity\Po;
use Ach\PoManagerBundle\Entity\Bpo;
use Ach\PoManagerBundle\Entity\Status;
use Ach\PoManagerBundle\Entity\Notification;
use Ach\PoManagerBundle\Entity\PoItem;
use Ach\PoManagerBundle\Entity\Price;
use Ach\PoManagerBundle\Entity\Invoice;
use Ach\PoManagerBundle\Entity\Revision;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;

use Ach\PoManagerBundle\Form\ProductSearchPnType;
use Ach\PoManagerBundle\Form\ProductSearchCustPnType;
use Ach\PoManagerBundle\Form\ProductSearchDescType;
use Ach\PoManagerBundle\Form\ParsePoType;
use Ach\PoManagerBundle\Form\PoType;
use Ach\PoManagerBundle\Form\EditPoType;
use Ach\PoManagerBundle\Form\BpoType;
use Ach\PoManagerBundle\Form\InvoiceType;
use Ach\PoManagerBundle\Form\ParseShipmentBatchType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PoManagerCreateEntryController extends Controller
{

	public function createPoAction()
	{
		// create the form based on an pre-filled instance of Po (using the parser)
		$parsePo = new Po();
		$formParsePo = $this->createForm(new ParsePoType, $parsePo);
		
		// create the form based on an empty instance of Po
		$po = new Po();
		$formPo = $this->createForm(new PoType, $po);
		
		$request = $this->get('request');
		if ($request->getMethod() == 'POST')
		{
			// When coming from the index page with pdf uploaded
			// (the formParsePo object contains the pdf file)
			$formParsePo->bind($request);
			if($formParsePo->isValid())
			{
				if($parsePo->getFile() === null)
				{
					return $this->renderErrorPage("no file parameter passed");
				}
				else
				{
					// parse the PDF file and get the list of items
					$shoppingList = $parsePo->parsePdfFile();
					
					// get parsed info and create pre-filled form for each item
					$iteration = 0;
					$amount = 0;
					foreach($shoppingList as $shoppingItem)
					{
						$revision = $this->checkRevisionExists($shoppingItem['SKREV'], $shoppingItem['SKPN']);
						if(!$revision)
						{
							return $this->renderErrorPage("Product SK P/N " . $shoppingItem['SKPN'] . " with Revision " . $shoppingItem['SKREV'] . " invalid.");
						}
						
						// if unit price does not match return error
						if($revision->getProduct()->getPrice()->getPrice() != $shoppingItem['PRICE'])
						{
							return $this->renderErrorPage("Price are not matching with database for item P/N: " . $revision->getProduct()->getPn() . ", " . $revision->getProduct()->getDescription() . " - Please contact your account manager.");
						}
						/*echo '<br />';
						echo 'Price: ' . $revision->getProduct()->getPrice()->getPrice();
						echo 'P/N: ' . $revision->getProduct()->getPn();
						echo 'Desc: ' . $revision->getProduct()->getDescription();
						echo 'Price: ' . $shoppingItem['PRICE'];
						if ($revision->getProduct()->getPrice()->getPrice() == $shoppingItem['PRICE']) echo 'OKAY';
						echo '<br />';
						*/
						
						//get history of the product (last order information)
						$historyInfo = $this->getHistoryInfo($revision->getProduct()->getPn());
						
						// if revision and price matches, hydrate poItem instance
						$iteration++;
						$poItem = new \Ach\PoManagerBundle\Entity\PoItem();
						$poItem
							->setLineNum($iteration)
							->setPnF($revision->getProduct()->getPn())
							->setCustPnF($revision->getProduct()->getCustPn())
							->setRevisionF($revision->getRevisionCust())
							//->setDescription($shoppingItem['DESC'])
							->setDescription($revision->getProduct()->getDescription())
							->setQty($shoppingItem['QTY'])
							->setDueDate(new \DateTime($shoppingItem['NBD']))
							->setPriceF($revision->getProduct()->getPrice()->getPrice())
							// ->setTotalPriceF($revision[0]->getProduct()->getPrice()->getPrice() * $shoppingItem['QTY'])
							->setHistoryF($historyInfo)
						;
						
						//echo 'Total price: ' . $revision[0]->getProduct()->getPrice()->getPrice() . '*' . $shoppingItem['QTY'] . '=' . $poItem->getTotalPriceF();
						//echo '<br />';
						
						// add this item to the PO
						$parsePo->addPoItem($poItem);
						
						// add item price to amount
						// $amount += $poItem->getTotalPriceF();
						$amount += $shoppingItem['QTY'] * $revision->getProduct()->getPrice()->getPrice();
						//echo '<br />';
						//echo $amount;
						
					}
					
					// check total amount is correct: compare [PO parsed total amount] to [calculated total amount]
					// Note: due to binary representation of double, value is not exact
					// var_dump(abs($parsePo->getTotalAmount() - $amount));		    
					if(abs($parsePo->getTotalAmount() - $amount) > 0.001 )
					{
						return $this->renderErrorPage("Total amount does not match. PO entry has not been created.");
					}
					
					// create the form based on the $parsePo instance
					$formPo = $this->createForm(new PoType, $parsePo);
					return $this->render('AchPoManagerBundle:PoManager:createPo.html.twig', array('formPo' => $formPo->createView(), 'createOrModif' => 'Create', 'poCurrency' => $parsePo->getCurrency()));
					
				}
			}
			
			// when this page has been submitted, processing is the following:
			$formPo->bind($request);
			if($formPo->isValid())
			{
				/*-echo $po->getNum();
				echo '<br />';
				echo $po->getRelNum();
				echo '<br />';
				echo $po->getId();
				echo '<br />';*/
				
				$repository = $this->getDoctrine()
						->getManager()
						->getRepository('AchPoManagerBundle:Po');
				
				$similarPo = $repository->findOneBy(
						array ('num' => $po->getNum(), 'relNum' => $po->getRelNum())
						);
				
				
				if($similarPo != NULL)
				{
					return $this->renderErrorPage("PO with same number already exists. PO entry has not been created.");
				}
				
				$em = $this->getDoctrine()->getManager();
				
				if($po->getIsBpo())
				{
					$repositoryBpo = $this->getDoctrine()
							->getManager()
							->getRepository('AchPoManagerBundle:Bpo');
					$bpo = $repositoryBpo->findOneByNum($po->getNum());
					if(empty($bpo))
					{
						return $this->renderErrorPage("You have checked the BPO option but the BPO number " . $po->getNum() . " does not exist in the database. Please ask the database administrator to create BPO first. PO entry has not been created.");
					}
					else
					{
						$po->setBpo($bpo);
					}
				}
				
				
				
				foreach($po->getPoItems() as $poIt)
				{
					
					if($this->linkItemToDatabase($poIt))
					{
						return $this->renderErrorPage("Product with Cust. P/N " . $poIt->getCustPnF() . " (P/N " . $poIt->getPnF() . ") with Revision " . $poIt->getRevisionF() . " invalid on line " . $poIt->getLineNum());
					}
					$poIt->setPo($po);
					
				}
				
				
				// upload actual file in storage location defined in the parameter.ini
				$po->uploadFile($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('po_files_path'));
				
				$em->persist($po);
				
				$em->flush();
				
				return $this->render('AchPoManagerBundle:PoManager:successPoCreated.html.twig', array('po' => $po));
				// return new Response("New PO entry successfully added to Po Manager");
			}
		}
		else
		{
			// fill out the form from scratch
			return $this->render('AchPoManagerBundle:PoManager:createPo.html.twig', array('formPo' => $formPo->createView(), 'poCurrency' => 'EUR', 'createOrModif' => 'Create' ));
		}
    }

	
	public function modifyPoAction(Po $originalPo, Request $request)
    {
		
		// create the form based on an empty instance of Po
		$modifPo = new Po();
		$modifFormPo = $this->createForm(new EditPoType, $modifPo);
		
		$em = $this->getDoctrine()->getManager();
		
		if ($request->getMethod() == 'POST')
		{
			// When coming from the index page with pdf uploaded
			// (the formParsePo object contains the pdf file)
			$modifFormPo->bind($request);
			if($modifFormPo->isValid())
			{
				if($modifPo->getFile() === null)
				{
					return $this->renderErrorPage("no file parameter passed");
				}
				
				// process the modification
				$listPoItems = $originalPo->getPoItems();
				foreach($listPoItems as &$originalItem)
				{
					
					$doesNotExistInModif = true;
					foreach($modifPo->getPoItems() as $modifItem)
					{
						if($modifItem->getLineNum() == $originalItem->getLineNum())
						{
							// same item at the same line
							if(($modifItem->getPnF() == $originalItem->getRevision()->getProduct()->getPn()) and $doesNotExistInModif)
							{
								$doesNotExistInModif = false;
								
								// same item has quantity or due date modified or comment modified
								if($modifItem->getQty() != $originalItem->getQty() or $modifItem->getDueDate() != $originalItem->getDueDate() or $modifItem->getComment() != $originalItem->getComment())
								{
									// check if item hasn't shipped and therefore can still be modified
									if($originalItem->getShippedQty() == 0)
									{
										$originalItem->setQty($modifItem->getQty());
										$originalItem->setDueDate($modifItem->getDueDate());
										$originalItem->setComment($modifItem->getComment());
										
										//reset status to "IN REVIEW" since it needs to be approved again by prod Manager
										$repositoryStatus = $this->getDoctrine()
															->getManager()
															->getRepository('AchPoManagerBundle:Status');
										$originalItem->setStatus($repositoryStatus->findOneByName('IN REVIEW'));
										$originalItem->setApproved(false);
										
										// send notification of modification of this item
										$notification = $this->get('ach_po_manager.notification_creator')->createNotification($originalItem, "MODIFIED ORDER NOTIFICATION");
										$em->persist($notification);
									}
									// if item already shipped, then send error message
									else
									{
										return $this->renderErrorPage("Item on line " . $modifItem->getLineNum() . " P/N " . $modifItem->getPnF() . " (Cust. P/N " . $modifItem->getCustPnF() . ") has already shipped, cannot be modified. PO entry has not been created.");
									}
									
								}
								// if same item with same quantity and same due date
								// no action
								
							}
							// different item at same line: create the new item
							else
							{
								// connect the instance of item to other database table
								if($this->linkItemToDatabase($modifItem))
								{
									// if P/N with specific Rev does not exist, return error message
									return $this->renderErrorPage("Product with Cust. P/N " . $modifItem->getCustPnF() . " (P/N " . $modifItem->getPnF() . ") with Revision " . $modifItem->getRevisionF() . " invalid on line " . $modifItem->getLineNum() . ". PO entry has not been created.");
								}
								$originalPo->addPoItem($modifItem);
								$em->persist($modifItem);
								
							}
						}
					}
					// same item at the same line was not find in the modified Po
					if($doesNotExistInModif)
					{
						// check if item can still be deleted
						/*
						if($originalItem->getShippedQty() == 0)
						{
							$originalPo->removePoItem($originalItem);
							// send notification of cancelled item
							//$notification = $this->get('ach_po_manager.notification_creator')->createNotification($poIt, "CANCELLED ORDER NOTIFICATION");
						
						}
						// if item already shipped, then send error message
						else
						{
							return new Response("Item has already shipped, cannot change it");
						}
						*/
						// deleting item is not authorized
						return $this->renderErrorPage("Deleting item is not authorized. If you wish to do so, please contact database administrator. PO entry has not been created.");
					}
				}
				
				// another loop in loop to find potential new line number
				foreach($modifPo->getPoItems() as $modifItem)
				{
					$doesNotExistInOriginal = true;
					foreach($listPoItems as $originalItem)
					{
						if($modifItem->getLineNum() == $originalItem->getLineNum())
						{
							$doesNotExistInOriginal = false;
						}
					}
					if($doesNotExistInOriginal)
					{
						if($this->linkItemToDatabase($modifItem))
						{
							// if P/N with specific rev does not exist in database does not exist, then display error message
							return $this->renderErrorPage("Product with Cust. P/N " . $modifItem->getCustPnF() . " (P/N " . $modifItem->getPnF() . ") with Revision " . $modifItem->getRevisionF() . " invalid. PO entry has not been created.");
						}
						$originalPo->addPoItem($modifItem);
						$em->persist($modifItem);
						
					}
				}
				
				// replace old file by new file
				$originalPo->setFile($modifPo->getFile());
				// upload new file in storage location defined in the parameter.ini
				$originalPo->uploadFile($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('po_files_path'));
				
				
				$em->flush();
				
				//return new Response('PO successfully modified');
				return $this->render('AchPoManagerBundle:PoManager:successPoModified.html.twig', array('po' => $originalPo));
			}
		}
		else
		{
			// populate the field all the poItem
			foreach($originalPo->getPoItems() as $poItem)
			{
				// set all F attribute
				$poItem->setAllF();
			}
			
			// create and render form
			$formPo = $this->createForm(new EditPoType, $originalPo);
			return $this->render('AchPoManagerBundle:PoManager:createPo.html.twig', array('formPo' => $formPo->createView(), 'poCurrency' => $originalPo->getCurrency(), 'createOrModif' => 'Modify' ));
		}
	}
	
	
	/* Invoice the PoItem*/
	public function createInvoiceAction()
	{
		// get the file under type UploadedFile and shipmentItem id
		$request = $this->get('request');
		$invoiceFile = $request->files->get('invoiceFile');
		$invoiceComment = $request->request->get('invoiceComment');
		$parametersKeys = $request->request->keys();
		
		// create new instance of Invoice with comment and file that was passed: this file has to be PDF
		$invoice = new Invoice($invoiceFile, $invoiceComment);
		
		// get the repository for ShipmentItem
		$repositoryShipmentItem = $this->getDoctrine()
				->getManager()
				->getRepository('AchPoManagerBundle:ShipmentItem');
		
		foreach($parametersKeys as $parameterKey)
		{
			if(strpos($parameterKey, 'shipmentItem_') !== FALSE )
			{
				$parameter = $request->request->get($parameterKey);
				$invoice->addShipmentItem($repositoryShipmentItem->find($parameter));
				//$shipmentItem[$parameter] = $repository->find($parameter);
			}
		}
		
		// if coming from the very same page... then no invoiceFile is set
		if(!(isset($invoiceFile)))
		{
			throw $this->createNotFoundException('No invoice file passed');
		}
		
		// parse the file to get the invoice date and the invoice number
		$invoice->parsePdfFile();
		
		// if parsing worked fine then upload the invoice PDF file to final location
		$invoice->uploadFile($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('invoice_files_path'));
		
		// create a new notification with category "INVOICE NOTIFICATION"
		$notification = $this->get('ach_po_manager.notification_creator')->createNotification($invoice, "INVOICE NOTIFICATION");
		
		// Persist entries in database
		$em = $this->getDoctrine()->getManager();
		$em->persist($invoice);
		$em->persist($notification);
		$em->flush();
		
		// create the form based on the $invoice instance
		$formInvoice = $this->createForm(new InvoiceType, $invoice);
		return $this->render('AchPoManagerBundle:PoManager:createInvoice.html.twig', array('formInvoice' => $formInvoice->createView(), 'shipmentItems' => $invoice->getShipmentItems()));
		
		//PoManagerControllerUtility::parseInvoice($invoiceFile)
		
		/*
		// access or create invoice information in the database
		$repository = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:Invoice');
		$invoice = $repository->findOneByNum($invoiceNum);
		// if no Invoice entry has this invoice number yet, then create one invoice entry in the database
		if(empty($invoice))
		{
			//echo ' - create new shipment entry - ';
			$invoice = new Invoice();
			$invoice->setNum($invoiceNum);
		}
		
		// get the invoice date from the URL
		$request = $this->get('request');
		$invoiceDate = $request->query->get('date');
		
		// get the Po Item id
		// $parameters = $request->query->all();
		$parametersKeys = $request->query->keys();

		$repository = $this->getDoctrine()
					->getManager()
					->getRepository('AchPoManagerBundle:PoItem');
		
		// foreach($parameters as $parameter)
		foreach($parametersKeys as $parameterKey)
		{
			if(strpos($parameterKey, 'poItem_') !== FALSE )
			{
				$parameter = $request->query->get($parameterKey);
				//echo 'poItem: ' . $parameter . ' - ';
				$poItem = $repository->find($parameter);
				if(empty($poItem))
				{
					return new Response("Error: invalid PO item with id: " . $parameter);
				}
				$poItems[] = $poItem;
			}
		}
		
		if(empty($poItems))
		{
			return new Response("No Po item found ");
		}
		
		foreach($poItems as $poItem)
		{
			echo ' po id: ';
			echo $poItem->getId();
			echo ' - ';
			// tie shipment info (ie. tracking number) to poItem
			//$poItem->setShipment($shipment);
		}
		*/
		
	}
	
	/**
	* Create Bpo
	**/
	public function createBpoAction(Revision $revision, Request $request)
	{
		// create new Bpo entity instance with path to the directory where BPO file pdf are stored
		$bpo = new Bpo($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('bpo_files_path'));
		
		if ($request->getMethod() == 'POST')
		{
			$formBpo = $this->createForm(new BpoType, $bpo);
			
			$formBpo->bind($request);
			if($formBpo->isValid())
			{
				$bpo->setRevision($revision);
				$bpo->setPrice($revision->getProduct()->getPrice());
				
				$em = $this->getDoctrine()->getManager();
				
				$em->persist($bpo);
				
				// echo $bpo->getFilePath();
				
				// create a new notification with category "NEW BPO NOTIFICATION"
				$notification = $this->get('ach_po_manager.notification_creator')->createNotification($bpo, "NEW BPO NOTIFICATION");
				
				$em->persist($notification);
				
				$em->flush();
				
				return new Response('new BPO entered successfully: ' . $bpo->getNum());
				
			}
		
		}
		
		else
		{
			//$bpo->setPrice($revision->getProduct()->getPrice());
			//$bpo->setRevisionF($revision->getId());
			$bpo->setPriceF($revision->getProduct()->getPrice()->getPrice());
			$bpo->setRevisionF($revision->getRevisionCust());
			$bpo->setDescriptionF($revision->getProduct()->getDescription());
			
			$formBpo = $this->createForm(new BpoType, $bpo);
			
			return $this->render('AchPoManagerBundle:PoManager:createBpo.html.twig', array('formBpo' => $formBpo->createView()));
		}
		
		// new Response($revision->getRevisionCust());
	}

    /**
     *
     * Create Batch
     *
    **/
    public function createShipmentBatchAction()
    {
  		// create the form based on an pre-filled instance of ShipmentBatch (using the parser)
        $shipmentBatch = new ShipmentBatch($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('zip_files_path'));
		$formParseShipmentBatch = $this->createForm(new ParseShipmentBatchType, $shipmentBatch);

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
		{
			$formParseShipmentBatch->bind($request);
			if($formParseShipmentBatch->isValid())
			{
                if($shipmentBatch->getFile() === null)
				{
					return $this->renderErrorPage("no file parameter passed");
				}
				else
				{
                    $file = $shipmentBatch->getFile();
                    $filename = $file->getClientOriginalName();

                    // if file already exist in database, then return error
                    // get the repository for ShipmentItem
                    $repositoryShipmentBatch = $this->getDoctrine()
                                                   ->getManager()
                                                   ->getRepository('AchPoManagerBundle:ShipmentBatch');
                    if($repositoryShipmentBatch->findOneByFilePath($filename) != null)
                    {
                        return $this->renderErrorPage("File $filename has already been recorded");
                    }

                    preg_match("/(\d+)_(\S+)_lot.zip/", $filename, $output_array);

                    if(count($output_array) <2 )
                    {
                        return $this->renderErrorPage("File name $filename invalid, are you sure it's a regular zip lot file?");
                    }

                    $product = $output_array[2];
                    $lotNum =  $output_array[1];

                    //echo "Uploading Production ZIP file \n";
                    //echo "Product: " . $product . "\n";
                    //echo "Lot Number: " . $lotNum . "\n";

                    $shipmentBatch->setProductName($product);
                    $shipmentBatch->setNum($lotNum);

                    $za = new \ZipArchive();

                    if ($za->open($file, \ZipArchive::CREATE)!==TRUE) {
                        return $this->renderErrorPage("Unable to open zip file $filename.");
                    }

                    //echo "Number of files in the archive: " . $za->numFiles . "\n";
                    ////echo "status:" . $za->status . "\n";
                    ////echo "statusSys: " . $za->statusSys . "\n";
                    ////echo "filename: " . $za->filename . "\n";
                    ////echo "comment: " . $za->comment . "\n";

                    //verify if number of file in the archive is normal
                    $correctNumFile = $this->container->getParameter('lot_' . $product);
                    $numFiles = $za->numFiles;
                    if($numFiles != $correctNumFile)
                        return $this->renderErrorPage("$filename contains $za->numFiles ini files, $product zip should contain $correctNumFile files!");

                    //echo "========================\n";

                    // parse each ini file one by one and if correct record them in database
                    for ($i=0; $i<$za->numFiles;$i++) {
                        //echo "ini file number ". ($i+1) . "\n";
                        $iniFiles[$i]['fileNum'] = $i+1;
                        $iniName = $za->getNameIndex($i);
                        //echo $iniName . "\n";
                        $iniFiles[$i]['iniName'] = $iniName; 
                        $iniContent = $za->getFromIndex($i);
                        //echo $iniContent;
                        $iniFiles[$i]['iniContent'] = $iniContent; 
                        preg_match("/[0-9A-Fa-fxX]{2}(-[0-9A-Fa-fxX]{2}){5}/", $iniName, $output_array);
                        $macAddress = $output_array[0];
                        preg_match("/-SYS ([A-Z]\d{7})_/", $iniName, $output_array);
                        $sn = $output_array[1];
                        
                        if(strpos($iniName, $product) !== false and strpos($iniContent, $macAddress) and strpos($iniContent, $sn) and strpos($iniContent, "Overall=Pass"))
                        {
                            //echo "ini file is correct\n";
                            //echo "MAC address: " . $macAddress . "\n";
                            $iniFiles[$i]['macAddress'] = $macAddress; 
                            //echo "S/N: " . $sn . "\n";
                            $iniFiles[$i]['sn'] = $sn; 
                            $serialNumber = new SerialNumber($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('zip_files_path'));
                            $serialNumber->setSerialNumber($sn);
                            $serialNumber->setMacAddress(preg_replace("/-/", "", $macAddress));
                            $serialNumber->setShipmentBatch($shipmentBatch);
                            $serialNumber->setCertificateFileName($iniName);
                            $serialNumber->setComment($iniContent);
                            $em->persist($serialNumber);
                        }
                        else
                        {
                            return $this->renderErrorPage("File $iniName in $filename is not correct");
                        }
                        //$za->extractTo(".", $iniName);
                        //print_r($za->statIndex($i));
                        //echo "--------------------\n";
                    }

                    $em->persist($shipmentBatch);
                    $em->flush();

                    //echo getcwd();

                    $za->close();

                    return $this->render('AchPoManagerBundle:PoManager:successLotCreated.html.twig', array(
                        'filename' => $filename,
                        'product' => $product,
                        'lotNum' => $lotNum,
                        'numFiles' => $numFiles,
                        'iniFiles' => $iniFiles,
                        'comment' =>  $shipmentBatch->getComment()
                    ));
                    
                    //return new Response('Success creating ZIP ' . $file->getClientOriginalName());
                }
                
            }

        }

    }
	
	
	//////
	//
	// Private function Section
	//
	//////

	private function linkItemToDatabase($poIt)
	{
		$em = $this->getDoctrine()->getManager();
		
		$revision = $this->checkRevisionExists($poIt->getRevisionF(), $poIt->getCustPnF());
		if(!$revision)
		{
			//echo "Product SK P/N " . $poIt->getCustPnF() . " with Revision " . $poIt->getRevisionF() . " invalid";
			return true;
		}
		
		
		$poIt->setRevision($revision);
	    
		// process special case when non prod PO item
		// when non prod, there is no default pricing coz product does not exist
		// furthermore, price can be anything
		// so need to create price entry in the database price table
		if(strpos($poIt->getPnF(),'None') !== false)
		{
			$createdPrice = new Price();
			$currency = $revision->getProduct()->getPrice()->getCurrency();
			$createdPrice->setCurrency($currency);
			$createdPrice->setPrice($poIt->getPriceF());
			$em->persist($createdPrice);
			$poIt->setPrice($createdPrice);
			$poIt->setDescription("NON-PROD ITEM: " . $poIt->getComment());
			$poIt->setComment(null);
		}
		// if normal production item...
		else
		{
			$poIt->setPrice($revision->getProduct()->getPrice());
		}
		
		// if item Production Manager ID is 1, then follow complete workflow for notification and status
		if($poIt->getRevision()->getProduct()->getProdManager()->getId() == 1)
		{
			
			// set item in review stage
			$repositoryStatus = $this->getDoctrine()
				->getManager()
				->getRepository('AchPoManagerBundle:Status');
			$poIt->setStatus($repositoryStatus->findOneByName('IN REVIEW'));
			
			// create a new notification with category "NEW ORDER NOTIFICATION"
			$notification = $this->get('ach_po_manager.notification_creator')->createNotification($poIt, "NEW ORDER NOTIFICATION");
			
			$em->persist($notification);
		}
		else
		{
			// set item in approved stage
			$repositoryStatus = $this->getDoctrine()
				->getManager()
				->getRepository('AchPoManagerBundle:Status');
			$poIt->setStatus($repositoryStatus->findOneByName('APPROVED'));
			$poIt->setApproved(true);
			
			if($poIt->getRevision()->getProduct()->getProdManager()->getId() == 2)
			{
				// create a new notification with category "TEAM ORDER NOTIFICATION"
				$notification = $this->get('ach_po_manager.notification_creator')->createNotification($poIt, "TEAM ORDER NOTIFICATION");
				$em->persist($notification);
				
				// create a new notification with category "CONFIRM ORDER NOTIFICATION"
				$notification = $this->get('ach_po_manager.notification_creator')->createNotification($poIt, "CONFIRM ORDER NOTIFICATION");
				$em->persist($notification);
			}
		}
		return false;
	}

    // check if part number exists with specific rev and, if yes, then return it
    private function checkRevisionExists($skrev, $skpn)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Revision');

	// get revision instance
	$revision = $repository->findRevProduct($skrev, $skpn);

	// if this revision does not exist return error
	//if(count($revision) != 1)
	if($revision == null)
	{
	    return false;
	}
	else
	{
	    return $revision;
	}
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
	
	private function renderErrorPage($message)
	{
		return $this->render('AchPoManagerBundle:PoManager:error.html.twig', array('message' => $message));
	}
}
