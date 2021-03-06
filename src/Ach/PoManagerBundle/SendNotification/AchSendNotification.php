<?php

namespace Ach\PoManagerBundle\SendNotification;

use Ach\PoManagerBundle\ConnectProdDatabase\AchConnectProdDatabase;

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
	protected $connectProdDB;
	
	// query to remote production database string
    protected $sql_query_pattern; // = 'SELECT System_SN, Assembly_date, PSU_SN, Motherboard_SN, SK38_M_SN, LCD_SN, DDR1_SN, DDR2_SN, MACID1_MB, MACID2_MB, HDD_SN, SATADOM_SN, CARD_USB3_SN FROM SK38 WHERE System_SN like ';
	
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

	public function __construct(\Swift_Mailer $mailer, \Swift_Transport_EsmtpTransport $swiftmailer_transport_real, \Symfony\Bundle\FrameworkBundle\Routing\Router $router, \Symfony\Bundle\TwigBundle\TwigEngine $templating, \Liuggio\ExcelBundle\Factory $phpexcel, AchConnectProdDatabase $connectProdDB, $po_files_path, $bpo_files_path, $invoice_files_path, $rma_files_path, $from_emails, $external_lifesheet_select_query)
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
		$this->connectProdDB = $connectProdDB;
                $this->sql_query_pattern = $external_lifesheet_select_query;
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
                    'BillingManagerEmail'         => (null !== $notification->getRma()->getSerialNum()->getShipmentBatch()) ? $notification->getRma()->getSerialNum()->getShipmentBatch()->getShipmentItem()->getPoItem()->getRevision()->getProduct()->getBillingManager()->getEmail() : "",
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
				
                $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setWidth(18);
                $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                $phpExcelObject->getActiveSheet()->getColumnDimension('D')->setWidth(11);
				$phpExcelObject->getActiveSheet()->getColumnDimension('E')->setWidth(11);
				$phpExcelObject->getActiveSheet()->getColumnDimension('F')->setWidth(16);
				$phpExcelObject->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$phpExcelObject->getActiveSheet()->getColumnDimension('H')->setWidth(16);
				$phpExcelObject->getActiveSheet()->getColumnDimension('I')->setWidth(18);
				$phpExcelObject->getActiveSheet()->getColumnDimension('J')->setWidth(18);
				$phpExcelObject->getActiveSheet()->getColumnDimension('K')->setWidth(10);
				$phpExcelObject->getActiveSheet()->getColumnDimension('L')->setWidth(17);
				$phpExcelObject->getActiveSheet()->getColumnDimension('M')->setWidth(13);
				$phpExcelObject->getActiveSheet()->getColumnDimension('N')->setWidth(13);
				$phpExcelObject->getActiveSheet()->getColumnDimension('O')->setWidth(14);
				$phpExcelObject->getActiveSheet()->getColumnDimension('P')->setWidth(18);
				$phpExcelObject->getActiveSheet()->getColumnDimension('Q')->setWidth(14);

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
				
				$phpExcelObject->getActiveSheet()->getStyle('C6')->getFont()->setBold(true);
				
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('C6', 'VITEC hereby certifies that the products listed below have been manufactured and tested in accordance with applicable specifications, drawings and standards and in conformance with the requirements of the purchase order relative to this shipment');

                $phpExcelObject->getActiveSheet()->getStyle('A8:Q8')->getFont()->setBold(true);

		                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A8', 'LOT');
                		$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B8', 'S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('C8', 'Mfg. P/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('D8', 'Stryker P/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('E8', 'Stryker Rev.');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('F8', 'Assembly date');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('G8', 'Motherboard S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('H8', 'Capture card S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('I8', 'DDR1 S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('J8', 'DDR2 S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('K8', 'PSU S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('L8', 'LCD S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('M8', 'MAC ADDR 1');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('N8', 'MAC ADDR 2');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('O8', 'HDD S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('P8', 'SATADOM S/N');
				$phpExcelObject->setActiveSheetIndex(0)->setCellValue('Q8', 'CARD USB3 S/N');
				
				
                $index = 9;
                foreach($shipmentBatches as $lot)
                    {
                        $phpExcelObject->setActiveSheetIndex(0)->setCellValue('A' . $index, $lot->getProductName() . ' lot #' . $lot->getNum());
                        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A' . $index . ':A' . ($index+count($lot->getSerialNumbers())-1) );
                        $phpExcelObject->getActiveSheet()->getStyle('A' . $index . ':A' . ($index+count($lot->getSerialNumbers())-1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                        $sns = $lot->getSerialNumbers();

                        foreach($sns as $sn)
                            {
                                $phpExcelObject->setActiveSheetIndex(0)->setCellValue('B' . $index, $sn->getSerialNumber());
								$phpExcelObject->getActiveSheet()
									->getStyle('C' . $index)
									->getNumberFormat()
									->setFormatCode(
										\PHPExcel_Style_NumberFormat::FORMAT_TEXT
									);
									
								$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('C' . $index, $lot->getShipmentItem()->getPoItem()->getRevision()->getProduct()->getPn(), \PHPExcel_Cell_DataType::TYPE_STRING);
								$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('D' . $index, $lot->getShipmentItem()->getPoItem()->getRevision()->getProduct()->getCustPn(), \PHPExcel_Cell_DataType::TYPE_STRING);
								$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('E' . $index, 'Rev ' . $lot->getShipmentItem()->getPoItem()->getRevision()->getRevisionCust(), \PHPExcel_Cell_DataType::TYPE_STRING);
								
								//hard-coded for now...
								if($lot->getProductName() == "SK38" or $lot->getProductName() == "SR8")
								{
									try {
									// connect to the database
										$bdd = $this->connectProdDB->getPDO();
									}
									catch(\Exception $e) {
										throw new \Exception($notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " Error when trying to connect to prod database: ". $e->getMessage());
									}
									
									$sql_query = $this->sql_query_pattern[$lot->getProductName()] . "'" . $sn->getSerialNumber() ."';";
									
									$req = $bdd->prepare($sql_query);
									
									if($req->execute()) {
										$results = $req->fetchall();
										$req->closeCursor();
										if (count($results) > 1)
										{
											throw new \Exception($notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " Error when processing query on S/N " . $sn->getSerialNumber() . ", did return more than one instance");
										}
										if (count($results) == 0)
										{
											throw new \Exception($notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " Error when processing query on S/N " . $sn->getSerialNumber() . ", did not return any instance");
										}
										$result = $results[0];
										
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('F' . $index, $result['Assembly_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('G' . $index, $result['Motherboard_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										if($lot->getProductName() == "SK38") {
											$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('H' . $index, $result['SK38_M_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										}
										else {
											$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('H' . $index, $result['SR8_M_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										}
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('I' . $index, $result['DDR1_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('J' . $index, $result['DDR2_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('K' . $index, $result['PSU_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('L' . $index, $result['LCD_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('M' . $index, $result['MACID1_MB'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('N' . $index, $result['MACID2_MB'], \PHPExcel_Cell_DataType::TYPE_STRING);
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('O' . $index, $result['HDD_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										if($lot->getProductName() == "SK38") {
											$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('P' . $index, $result['SATADOM_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										}
										else {
											$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('P' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
										}
										$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('Q' . $index, $result['CARD_USB3_SN'], \PHPExcel_Cell_DataType::TYPE_STRING);
										
									}
									else
									{
										throw new \Exception($notification->getNotificationCategory()->getName() . " on ID# ".$notification->getSourceId() . " Error: Fail to execute query on prod database on S/N " . $sn->getSerialNumber() . $sql_query);
									}
								}
								else
								{
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('M' . $index, $sn->getMacAddress(), \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('F' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('G' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('H' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('I' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('J' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('K' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('L' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('N' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('O' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('P' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									$phpExcelObject->setActiveSheetIndex(0)->setCellValueExplicit('Q' . $index, 'N/A', \PHPExcel_Cell_DataType::TYPE_STRING);
									
								}

                                $index++;
                            }
                    }

                //$phpExcelObject->getActiveSheet()->getStyle('D7:D' . ($index-1))->getAlignment()->setWrapText(true);

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

                $phpExcelObject->getActiveSheet()->getStyle('A8:Q' . ($index-1))->applyFromArray($styleArray);

                $phpExcelObject->getActiveSheet()->setTitle('shipment details');
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $phpExcelObject->setActiveSheetIndex(0);
				
				
				$phpExcelObject->getSecurity()->setLockWindows(true);
				$phpExcelObject->getSecurity()->setLockStructure(true);
				$phpExcelObject->getSecurity()->setWorkbookPassword("12345");
				$phpExcelObject->getActiveSheet()->getProtection()->setSheet(true);
				$phpExcelObject->getActiveSheet()->getProtection()->setPassword("12345");


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
                $email->attach(\Swift_Attachment::fromPath($ftemp)->setFilename("Lifesheet_CoC_$shipmentId.xlsx"));
                
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
