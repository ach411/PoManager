<?php

namespace Ach\PoManagerBundle\SendNotification;

class AchSendNotification
{
	protected $mailer;
	protected $router;
	protected $template;
    protected $phpexcel;
	protected $po_files_path;
	protected $bpo_files_path;
	protected $invoice_files_path;
    protected $swiftmailer_transport_real;
    protected $from_emails;
	
	// take string(s) in message_pattern
	// and resolve variable %var% by their value define in replace_string_array
	// replace_string_array ( key => value )
	// key represent the var to replace
	// value is the new value
	// message_pattern can be a string or an array of string
	private function resolveVariable($replace_string_array, $message_pattern)
	{
		
		$search = array_keys($replace_string_array);
		
		foreach($search as &$var)
		{
			$var = '%'.$var.'%';
		}
		
		if(is_string($message_pattern))
		{
			return str_replace($search, $replace_string_array, $message_pattern);
		}
		
		elseif(is_array($message_pattern))
		{
			foreach($message_pattern as $mp)
			{
				$message_pattern_array[] = str_replace($search, $replace_string_array, $mp);
			}
			return $message_pattern_array;
		}
	
	}
	
	private function filterEmailArray($email_array, $log)
	{
		foreach($email_array as $key => $email)
		{
			if(filter_var($email, FILTER_VALIDATE_EMAIL) == false)
			{
				$log .= '---Warning: delete invalid email entry : '.$email;
				unset($email_array[$key]);
			}
		}
		return $email_array;
	}

	public function __construct(\Swift_Mailer $mailer, \Swift_Transport_EsmtpTransport $swiftmailer_transport_real, \Symfony\Bundle\FrameworkBundle\Routing\Router $router, \Symfony\Bundle\TwigBundle\TwigEngine $templating, \Liuggio\ExcelBundle\Factory $phpexcel, $po_files_path, $bpo_files_path, $invoice_files_path, $rma_files_path, $from_emails)
	{
		$this->mailer = $mailer;
		$this->router = $router;
		$this->template = $templating;
        $this->phpexcel = $phpexcel;
		$this->po_files_path = $po_files_path;
		$this->bpo_files_path = $bpo_files_path;
		$this->invoice_files_path = $invoice_files_path;
        $this->rma_files_path = $rma_files_path;
		$this->swiftmailer_transport_real = $swiftmailer_transport_real;
        $this->from_emails = $from_emails;
	}

	/**
	 * Take a generic message text pattern
	 * and replace all the variables by their value
	 * a variable is designated by % sign, for instance: %variable%
	 * 
	 * @param string $msgPattern, array $substitutes
	 */
	public function sendNotification($notification, $files_root_path)
	{
		// get the message pattern to hydrate with actual values
		// $msgPattern = $notification->getHistoryStatus()->getPoItem()->getRevision()->getProduct()->getCoordinator()->getMessage();
		//$msgPattern = $notification->getNotificationCategory()->getMessage();
		//$subjectPattern = $notification->getNotificationCategory()->getSubject();
		
		// get the patterns from the Notification Category instance and reformat them if need be
		$textPatterns = $notification->getNotificationCategory()->getTextFields(); // get array of indexes 'name' (category name), 'message' (generic pattern message), 'subject' (mail subject), 'attachedFile' (full file path)
		
		$emailPatterns = $notification->getNotificationCategory()->getEmailFields(); // get array of indexes 'sendTo', 'ccTo', 'bccTo'
		foreach($emailPatterns as $key => $pattern)
		{
			$emailPatternsFormat[$key] = preg_split("/[\s,;]+/",$pattern); // split string using ' ', ',' or ';' separator and return array of email(s)
		}
		
		$notificationPatterns = array_merge($textPatterns, $emailPatternsFormat); // combine text array and email array into one array
		
		//print_r($notificationPatterns);
		
		
		// define values of variable in the pattern (list to be completed)
		// in the message pattern, surround variable by 2 percentage sign (e.g. %variable%).
		// Depending on NotificationSource, variables found in message shall be resolve differently
		switch($notification->getNotificationSourceClass())
		{
			case "PoItem":
				
				$prodManagerId = $notification->getPoItem()->getRevision()->getProduct()->getProdManager()->getId();
				$shippingManagerId = $notification->getPoItem()->getRevision()->getProduct()->getShippingManager()->getId();
				
				$variableDefArray = array(
					'notificationCategory'		=> $notification->getNotificationCategory()->getName(),
					'status'					=> $notification->getPoItem()->getStatus()->getName(),
					'buyerEmail'				=> $notification->getPoItem()->getPo()->getBuyerEmail(),
					'poNum'						=> $notification->getPoItem()->getPo()->getNum(),
					'relNum'					=> $notification->getPoItem()->getPo()->getRelNum(),
					'lineNum'					=> $notification->getPoItem()->getLineNum(),
					'pn'						=> $notification->getPoItem()->getRevision()->getProduct()->getPn(),
					'qty'						=> $notification->getPoItem()->getQty(),
					'unit'						=> $notification->getPoItem()->getRevision()->getProduct()->getUnit()->getName(),
					'desc'						=> $notification->getPoItem()->getDescription(),
					'dueDate'					=> $notification->getPoItem()->getDueDate()->format('M d Y'),
					'comment'					=> $notification->getPoItem()->getComment(),
					'ProdManagerEmail'			=> $notification->getPoItem()->getRevision()->getProduct()->getProdManager()->getEmail(),
					'ShippingManagerEmail'		=> $notification->getPoItem()->getRevision()->getProduct()->getShippingManager()->getEmail(),
					'BillingManagerEmail'		=> $notification->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail(),
					'ProdManagerName'			=> $notification->getPoItem()->getRevision()->getProduct()->getProdManager()->getName(),
					'ShippingManagerName'		=> $notification->getPoItem()->getRevision()->getProduct()->getShippingManager()->getName(),
					'BillingManagerName'		=> $notification->getPoItem()->getRevision()->getProduct()->getBillingManager()->getName(),
					'custPn'					=> $notification->getPoItem()->getRevision()->getProduct()->getCustPn(),
					'revision'					=> $notification->getPoItem()->getRevision()->getRevision(),
					'revisionCust'				=> $notification->getPoItem()->getRevision()->getRevisionCust(),
					'ProcessPoItemProdLink'		=> $this->router->generate('ach_po_manager_process_poitem_prod', array('prodManagerId' => $prodManagerId), true),
//					'ProcessPoItemProdLink'		=> str_replace("localhost",gethostname(),$this->router->generate('ach_po_manager_process_poitem_prod', array('prodManagerId' => $prodManagerId), true)),
					'ProcessPoItemShippingLink'	=> $this->router->generate('ach_po_manager_process_poitem_ship', array('shippingManagerId' => $shippingManagerId), true)
				);
				
				break;
				
				
			case "Shipment":
				
				// get the ShipmentItem collection
				$listItems = $notification->getShipment()->getShipmentItems();
				
				// get the list message pattern
				$listMessagePattern = $notification->getNotificationCategory()->getListMessage();
				
				// prepare var to store concatenate item list
				$listItemResolved = "";

                // create variable to store the various shipmentBatch(s) from all the ShipmentItem(s) of the shipment
                $shipmentBatches = new \Doctrine\Common\Collections\ArrayCollection();
				
				// resolve variable for each item of the list and concatenate them to a single string to form the message body
				foreach($listItems as $item)
				{
                    foreach($item->getShipmentBatches() as $itemShipmentBatches)
                        {
                            $shipmentBatches[] = $itemShipmentBatches;
                        }
					$buyerEmail= $item->getPoItem()->getPo()->getBuyerEmail();
					
					// same notification should be send to all the buyers that are concerned by the shipment
					if(!(in_array($buyerEmail, $notificationPatterns['sendTo'])))
					{
						$notificationPatterns['sendTo'][] = $buyerEmail;
					}
					
					$shippingManagerEmail= $item->getPoItem()->getRevision()->getProduct()->getShippingManager()->getEmail();
					
					// same notification should be cc to all the shipping manager that are concerned by the shipment
					if(!(in_array($shippingManagerEmail, $notificationPatterns['ccTo'])))
					{
						$notificationPatterns['ccTo'][] = $shippingManagerEmail;
					}
					
					$billingManagerEmail= $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail();
					
					// same notification should be cc to all the billing manager that are concerned by the shipment
					if(!(in_array($billingManagerEmail, $notificationPatterns['ccTo'])))
					{
						$notificationPatterns['ccTo'][] = $billingManagerEmail;
					}
					
					
					$variableItemDefArray = array(
						'status'					=> $item->getPoItem()->getStatus()->getName(),
						'buyerEmail'				=> $item->getPoItem()->getPo()->getBuyerEmail(),
						'poNum'						=> $item->getPoItem()->getPo()->getNum(),
						'relNum'					=> $item->getPoItem()->getPo()->getRelNum(),
						'lineNum'					=> $item->getPoItem()->getLineNum(),
						'pn'						=> $item->getPoItem()->getRevision()->getProduct()->getPn(),
						'qty'						=> $item->getPoItem()->getQty(),
						'shippedQty'				=> $item->getPoItem()->getShippedQty(),
						'shipmentQty'				=> $item->getQty(),
						'remainingQty'				=> $item->getPoItem()->getQty() - $item->getPoItem()->getShippedQty(),
						'unit'						=> $item->getPoItem()->getRevision()->getProduct()->getUnit()->getName(),
						'desc'						=> $item->getPoItem()->getDescription(),
						'dueDate'					=> $item->getPoItem()->getDueDate()->format('M d Y'),
						'comment'					=> $item->getPoItem()->getComment(),
						'ProdManagerEmail'			=> $item->getPoItem()->getRevision()->getProduct()->getProdManager()->getEmail(),
						'ShippingManagerEmail'		=> $item->getPoItem()->getRevision()->getProduct()->getShippingManager()->getEmail(),
						'BillingManagerEmail'		=> $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail(),
						'ProdManagerName'			=> $item->getPoItem()->getRevision()->getProduct()->getProdManager()->getName(),
						'ShippingManagerName'		=> $item->getPoItem()->getRevision()->getProduct()->getShippingManager()->getName(),
						'BillingManagerName'		=> $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getName(),
						'custPn'					=> $item->getPoItem()->getRevision()->getProduct()->getCustPn(),
						'revision'					=> $item->getPoItem()->getRevision()->getRevision(),
						'revisionCust'				=> $item->getPoItem()->getRevision()->getRevisionCust(),
					);
					
					
					$listItemResolved .= $this->resolveVariable($variableItemDefArray, $listMessagePattern);
					$listItemResolved .= "\n";
					
				}

                //if ($notification->getShipment()->getShipmentBatch())
                				
				
				$variableDefArray = array(
					// Section replace in the collection
					'notificationCategory'		=> $notification->getNotificationCategory()->getName(),
					'listItem'					=> $listItemResolved,
					'trackingNum'				=> $notification->getShipment()->getTrackingNum(),
					'carrierName'				=> $notification->getShipment()->getCarrier()->getName(),
					'shippingDate'				=> $notification->getShipment()->getShippingDate()->format('M d Y'),
					'carrierLink'				=> $notification->getShipment()->getCarrier()->getLink(),
					'shipmentId'				=> $notification->getShipment()->getId()
				);
				break;
				
			case "Invoice":
			
				//echo 'got Invoice';
				
				// get the collection
				$listItems = $notification->getInvoice()->getShipmentItems();
				
				// get the list message pattern
				$listMessagePattern = $notification->getNotificationCategory()->getListMessage();
				
				// prepare var to store concatenate item list
				$listItemResolved = "";
				
				// resolve variable for each item of the list and concatenate them to a single string to form the message body
				foreach($listItems as $item)
				{
					
					$accountPayableEmail = $item->getPoItem()->getRevision()->getProduct()->getCustomer()->getAccountPayableEmail();
					
					// same notification should be send to all the buyers that are concern by the shipment
					if(!(in_array($accountPayableEmail, $notificationPatterns['sendTo'])))
					{
						$notificationPatterns['sendTo'][] = $accountPayableEmail;
					}
					
					$billingManagerEmail= $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail();
					$buyerEmail= $item->getPoItem()->getPo()->getBuyerEmail();
					
					// same notification should be cc to all the billing manager that are concerned by the shipment
					if(!(in_array($billingManagerEmail, $notificationPatterns['ccTo'])))
					{
						$notificationPatterns['ccTo'][] = $billingManagerEmail;
					}
					
					// same notification should be cc to all buyer that are concerned by the shipment
					if(!(in_array($buyerEmail, $notificationPatterns['ccTo'])))
					{
						$notificationPatterns['ccTo'][] = $buyerEmail;
					}
					
					$variableItemDefArray = array(
						'status'					=> $item->getPoItem()->getStatus()->getName(),
						'buyerEmail'				=> $item->getPoItem()->getPo()->getBuyerEmail(),
						'poNum'						=> $item->getPoItem()->getPo()->getNum(),
						'relNum'					=> $item->getPoItem()->getPo()->getRelNum(),
						'lineNum'					=> $item->getPoItem()->getLineNum(),
						'pn'						=> $item->getPoItem()->getRevision()->getProduct()->getPn(),
						'qty'						=> $item->getPoItem()->getQty(),
						'shippedQty'				=> $item->getPoItem()->getShippedQty(),
						'shipmentQty'				=> $item->getQty(),
						'remainingQty'				=> $item->getPoItem()->getQty() - $item->getPoItem()->getShippedQty(),
						'unit'						=> $item->getPoItem()->getRevision()->getProduct()->getUnit()->getName(),
						'desc'						=> $item->getPoItem()->getDescription(),
						'dueDate'					=> (is_null($item->getPoItem()->getDueDate()) ? null : $item->getPoItem()->getDueDate()->format('M d Y')),
						'comment'					=> $item->getPoItem()->getComment(),
						'ProdManagerEmail'			=> $item->getPoItem()->getRevision()->getProduct()->getProdManager()->getEmail(),
						'ShippingManagerEmail'		=> $item->getPoItem()->getRevision()->getProduct()->getShippingManager()->getEmail(),
						'BillingManagerEmail'		=> $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail(),
						'ProdManagerName'			=> $item->getPoItem()->getRevision()->getProduct()->getProdManager()->getName(),
						'ShippingManagerName'		=> $item->getPoItem()->getRevision()->getProduct()->getShippingManager()->getName(),
						'BillingManagerName'		=> $item->getPoItem()->getRevision()->getProduct()->getBillingManager()->getName(),
						'custPn'					=> $item->getPoItem()->getRevision()->getProduct()->getCustPn(),
						'revision'					=> $item->getPoItem()->getRevision()->getRevision(),
						'revisionCust'				=> $item->getPoItem()->getRevision()->getRevisionCust(),
						'trackingNum'				=> $item->getShipment()->getTrackingNum(),
						'carrierName'				=> $item->getShipment()->getCarrier()->getName(),
						'shippingDate'				=> (is_null($item->getShipment()->getShippingDate()) ? null : $item->getShipment()->getShippingDate()->format('M d Y')),
						'carrierLink'				=> $item->getShipment()->getCarrier()->getLink(),
						'shipmentId'				=> $item->getShipment()->getId(),
					);
					
					
					$listItemResolved .= $this->resolveVariable($variableItemDefArray, $listMessagePattern);
					$listItemResolved .= "\n";
				}
				
				$variableDefArray = array(
					// Section replace in the collection
					'notificationCategory'		=> $notification->getNotificationCategory()->getName(),
					'listItem'					=> $listItemResolved,
					'invoiceComment'			=> $notification->getInvoice()->getComment(),
					'invoiceNum'				=> $notification->getInvoice()->getNum(),
					'filePath'					=> $this->invoice_files_path,
					'fileName'					=> $notification->getInvoice()->getFilePath()
				);
				
				break;
			
			case "Bpo":
				
				$pn = $notification->getBpo()->getRevision()->getProduct()->getPn();
				
				$variableDefArray = array(
					'notificationCategory'		=> $notification->getNotificationCategory()->getName(),
					'buyerEmail'				=> $notification->getBpo()->getBuyerEmail(),
					'bpoNum'					=> $notification->getBpo()->getNum(),
					'pn'						=> $notification->getBpo()->getRevision()->getProduct()->getPn(),
					'qty'						=> $notification->getBpo()->getQty(),
					'unit'						=> $notification->getBpo()->getRevision()->getProduct()->getUnit()->getName(),
					'desc'						=> $notification->getBpo()->getRevision()->getProduct()->getDescription(),
					'startDate'					=> (is_null($notification->getBpo()->getStartDate()) ? null : $notification->getBpo()->getStartDate()->format('M d Y')),
					'endDate'					=> (is_null($notification->getBpo()->getEnddate()) ? null : $notification->getBpo()->getEndDate()->format('M d Y')),
					'comment'					=> $notification->getBpo()->getComment(),
					'ProdManagerEmail'			=> $notification->getBpo()->getRevision()->getProduct()->getProdManager()->getEmail(),
					'ShippingManagerEmail'		=> $notification->getBpo()->getRevision()->getProduct()->getShippingManager()->getEmail(),
					'BillingManagerEmail'		=> $notification->getBpo()->getRevision()->getProduct()->getBillingManager()->getEmail(),
					'ProdManagerName'			=> $notification->getBpo()->getRevision()->getProduct()->getProdManager()->getName(),
					'ShippingManagerName'		=> $notification->getBpo()->getRevision()->getProduct()->getShippingManager()->getName(),
					'BillingManagerName'		=> $notification->getBpo()->getRevision()->getProduct()->getBillingManager()->getName(),
					'custPn'					=> $notification->getBpo()->getRevision()->getProduct()->getCustPn(),
					'revision'					=> $notification->getBpo()->getRevision()->getRevision(),
					'revisionCust'				=> $notification->getBpo()->getRevision()->getRevisionCust(),
					'SearchBpoByPnLink'			=> $this->router->generate('ach_po_manager_search_bpo_pn_num', array('pn' => $pn), true)
				);
				
				break;
                
            case "Rma":

                // get the collection
				$listItems = $notification->getRma()->getPartReplacements();
				
				// get the list message pattern
				$listMessagePattern = $notification->getNotificationCategory()->getListMessage();
				
				// prepare var to store concatenate item list
				$listItemResolved = "";
				
				// resolve variable for each item of the list and concatenate them to a single string to form the message body
				foreach($listItems as $item)
				{
                    $variableItemDefArray = array(
                        'pn'                    => $item->getProduct()->getPn(),
                        'custPn'                => $item->getProduct()->getCustPn(),
                        'productDescription'    => $item->getProduct()->getDescription()
                    );

                    $listItemResolved .= $this->resolveVariable($variableItemDefArray, $listMessagePattern);
					$listItemResolved .= "\n";
                }

                $variableDefArray = array(
					'notificationCategory'		  => $notification->getNotificationCategory()->getName(),
                    'listItem'					  => $listItemResolved,
                    'rmaSerialNumber'             => $notification->getRma()->getSerialNum()->getSerialNumber(),
                    'repairLocation'              => $notification->getRma()->getRepairLocation()->getAddress(),
                    'rmaNum'                      => $notification->getRma()->getNum(),
                    'RmaContactEmail'             => $notification->getRma()->getContactEmail(),
                    'comment'                     => $notification->getRma()->getComment(),
                    'investigationResult'         => $notification->getRma()->getInvestigationResult(),
                    'problemCategoryName'         => (null !== $notification->getRma()->getProblemCategory()) ? $notification->getRma()->getProblemCategory()->getName() : "",
                    'problemCategoryDescription'  => (null !== $notification->getRma()->getProblemCategory()) ? $notification->getRma()->getProblemCategory()->getDescription() : "",
                    'repairPo'                    => $notification->getRma()->getRpoNum(),
                    'trackingNum'				  => (null !== $notification->getRma()->getShipment() ) ? $notification->getRma()->getShipment()->getTrackingNum() : "",
					'carrierName'				  => (null !== $notification->getRma()->getShipment() ) ? $notification->getRma()->getShipment()->getCarrier()->getName() : "",
                    'carrierLink'                 => (null !== $notification->getRma()->getShipment() ) ? $notification->getRma()->getShipment()->getCarrier()->getLink() : "",
                    'BillingManagerName'          => (null !== $notification->getRma()->getSerialNum()->getShipmentBatch()) ? $notification->getRma()->getSerialNum()->getShipmentBatch()->getShipmentItem()->getPoItem()->getRevision()->getProduct()->getBillingManager()->getName() : "",
                    'BillingManagerEmail'         => (null !== $notification->getRma()->getSerialNum()->getShipmentBatch()) ? $notification->getRma()->getSerialNum()->getShipmentBatch()->getShipmentItem()->getPoItem()->getRevision()->getProduct()->getBillingManager()->getName() : "",
                    'rpoFileLink'                 => "http://" . $this->router->getContext()->getHost() . $this->rma_files_path . "/" . $notification->getRma()->getRpoFilePath()

                );

                break;				
				
			default:
				echo "Error: notificationSource can't be identified";
		}
		
		//print_r($notificationPatterns);
		
		foreach($notificationPatterns as $key => $pattern)
		{
			//echo $key . ':' . $pattern . "\n";
			$emailFields[$key] = $this->resolveVariable($variableDefArray, $pattern);
		}
		
		
		
		$log = "";
		// filter the email address to get rid of potential wrong one(s)
		$emailFields['sendTo'] = $this->filterEmailArray($emailFields['sendTo'], $log);
		$emailFields['ccTo'] = $this->filterEmailArray($emailFields['ccTo'], $log);
		$emailFields['bccTo'] = $this->filterEmailArray($emailFields['bccTo'], $log);
		
		//var_dump($emailFields);
		
		if(empty($emailFields['sendTo']) && empty($emailFields['ccTo']) && empty($emailFields['bccTo']))
		{
			return 'No email sent, list of recipient is empty (no SendTo, CcTo or BccTo)';
		}
		$email = \Swift_Message::newInstance()
			->setSubject($emailFields['subject'])
//			->setFrom(array('noreply@example.com' => 'The Example team'))
			->setFrom($this->from_emails['notification'])
//			->setBody($emailFields['message']);
			->setBody($this->template->render('AchPoManagerBundle:PoManager:email_pattern.html.twig', array('message' => $emailFields['message'])), 'text/html');
		if(!empty($emailFields['sendTo']))
			$email->setTo($emailFields['sendTo']);
		if(!empty($emailFields['ccTo']))
			$email->setCc($emailFields['ccTo']);
		if(!empty($emailFields['bccTo']))
			$email->setBcc($emailFields['bccTo']);
		if(!empty($emailFields['attachedFile']))
		{
			//Set the root path for the attached file (not very nice: to be changed later)
			//$email->attach(\Swift_Attachment::fromPath('/home/vitec/www'.$emailFields['attachedFile']));
			$email->attach(\Swift_Attachment::fromPath($files_root_path . $emailFields['attachedFile']));
			// test if this service is launched by CLI or by actual client
			/*if (strpos(getcwd(), 'PoManager/web') !== FALSE)
			    // client
			    $email->attach(\Swift_Attachment::fromPath('../..'.$emailFields['attachedFile']));
			elseif(strpos(getcwd(), 'PoManager') !== FALSE)
			    // script
			    $email->attach(\Swift_Attachment::fromPath('..'.$emailFields['attachedFile']));
			else
			    // script launch from user home directory (it's the case if crontab is used)*/
			
		}
        //elseif(isset($autogeneratedFileData))
        elseif(isset($shipmentBatches) and !$shipmentBatches->isEmpty())
            {
                $shipmentId = $notification->getShipment()->getId();
                $carrier = $notification->getShipment()->getCarrier()->getName();
                $tracking = $notification->getShipment()->getTrackingNum();
                $dateShip = $notification->getShipment()->getCreatedDate()->format('Y-m-d');

                $phpExcelObject = $this->phpexcel->createPHPExcelObject();

                $phpExcelObject->getProperties()->setCreator("ACH")
                               ->setLastModifiedBy("ACH")
                               ->setTitle("detail_shipment_$shipmentId");

                $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $phpExcelObject->getActiveSheet()->getColumnDimension('D')->setWidth(35);

                $phpExcelObject->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);
                
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A1', 'Shipment ID: ');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A2', 'Carrier: ');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A3', 'tracking number: ');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A4', 'Shipping date: ');

                $phpExcelObject->getActiveSheet()->getStyle('B1:B4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B1', $shipmentId); 
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B2', $carrier); 
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B3', $tracking); 
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B4', $dateShip);

                $phpExcelObject->getActiveSheet()->getStyle('A6:D6')->getFont()->setBold(true);

                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A6', 'LOT');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B6', 'S/N');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('C6', 'MAC Address');
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('D6', 'Comments');

                $index = 7;
                foreach($shipmentBatches as $lot)
                    {
                        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A' . $index, $lot->getProductName() . ' lot #' . $lot->getNum());
                        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A' . $index . ':A' . ($index+count($lot->getSerialNumbers())-1) );
                        $phpExcelObject->getActiveSheet()->getStyle('A' . $index . ':A' . ($index+count($lot->getSerialNumbers())-1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                        $sns = $lot->getSerialNumbers();

                        foreach($sns as $sn)
                            {
                                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B' . $index, $sn->getSerialNumber());
                                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('C' . $index, $sn->getMacAddress());
                                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('D' . $index, $sn->getComment());

                                $index++;
                            }
                    }

                $phpExcelObject->getActiveSheet()->getStyle('D7:D' . ($index-1))->getAlignment()->setWrapText(true);

                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        )
                    ),
                    'font' => array(
                        'name'	=> 'Calibri',
                        'size' => 11
                    )
                );

                $phpExcelObject->getActiveSheet()->getStyle('A6:D' . ($index-1))->applyFromArray($styleArray);

                $phpExcelObject->getActiveSheet()->setTitle('shipment details');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);

                // create the writer
                // $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
                $writer = $this->phpexcel->createWriter($phpExcelObject, 'Excel2007');
                
                // create a temp file
                $ftemp = tempnam(sys_get_temp_dir(), 'pomanagerxls');
                //$log .= $ftemp . " ";
                
                // create the response
                //$writer->save($files_root_path . "/../tmp/temp.xlsx" );
                $writer->save($ftemp);
                
                // attach the temp file
                $email->attach(\Swift_Attachment::fromPath($ftemp)->setFilename("Details_shipment_$shipmentId.xlsx"));
                
            }
		
            //echo getcwd();
            try {
                $this->mailer->send($email);
           
                $transport = $this->mailer->getTransport();
                if (!$transport instanceof \Swift_Transport_SpoolTransport) {
                    return;
                }
                
                $spool = $transport->getSpool();
                if (!$spool instanceof \Swift_MemorySpool) {
                    return;
                }
                
                $spool->flushQueue($this->swiftmailer_transport_real);
            }
            catch(\Exception $e) {
                throw new \Exception($notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " Error when sending email: ". $e->getMessage());
            }
            
            // delete temp file
            if(isset($ftemp))
                unlink($ftemp);
            
            //$nowDate = new \DateTime('NOW');
            //$log .= $nowDate->format('Y-m-d H:i:s') . " : " . $notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " sent successfully";
            $log .= $notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " sent successfully";
            /* $log = $nowDate->format('Y-m-d H:i:s') . " ---EMAIL SENT TO " . $emailFields['sendTo']; */
            /* // $log = "---EMAIL SENT TO " . $emailFields['sendTo']; */
            /* // $log .= "\n---CC TO: " . $emailFields['ccTo']; */
            /* $log .= "---CC TO: " . $emailFields['ccTo']; */
            /* // $log .= "\n---BCC TO: " . $emailFields['bccTo']; */
            /* $log .= "---BCC TO: " . $emailFields['bccTo']; */
            /* // $log .= "\n---SUBJECT: " . $emailFields['subject']; */
            /* $log .= "---SUBJECT: " . $emailFields['subject']; */
            /* // $log .= "\n---MESSAGE: " . $emailFields['message']; */
            /* //$log .= "---MESSAGE: " . $emailFields['message']; */
            
            return $log;
	}

}