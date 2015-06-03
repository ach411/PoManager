<?php

namespace Ach\PoManagerBundle\SendNotification;

class AchSendNotification
{
	protected $mailer;
	protected $router;
	protected $template;
	protected $po_files_path;
	protected $bpo_files_path;
	protected $invoice_files_path;
	protected $files_root_path;
	
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

	public function __construct(\Swift_Mailer $mailer, \Symfony\Bundle\FrameworkBundle\Routing\Router $router, \Symfony\Bundle\TwigBundle\TwigEngine $templating, $po_files_path, $bpo_files_path, $invoice_files_path, $files_root_path)
	{
		$this->mailer = $mailer;
		$this->router = $router;
		$this->template = $templating;
		$this->po_files_path = $po_files_path;
		$this->bpo_files_path = $bpo_files_path;
		$this->invoice_files_path = $invoice_files_path;
		$this->files_root_path = $files_root_path;
	}

	/**
	 * Take a generic message text pattern
	 * and replace all the variables by their value
	 * a variable is designated by % sign, for instance: %variable%
	 * 
	 * @param string $msgPattern, array $substitutes
	 */
	public function sendNotification($notification)
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
				
				// get the collection
				$listItems = $notification->getShipment()->getShipmentItems();
				
				// get the list message pattern
				$listMessagePattern = $notification->getNotificationCategory()->getListMessage();
				
				// prepare var to store concatenate item list
				$listItemResolved = "";
				
				// resolve variable for each item of the list and concatenate them to a single string to form the message body
				foreach($listItems as $item)
				{
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
						'trackingNum'				=> $item->getShipment()->getTrackingNum(),
						'carrierName'				=> $item->getShipment()->getCarrier()->getName(),
						'shippingDate'				=> $item->getShipment()->getShippingDate()->format('M d Y'),
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
			//->setFrom('noreply@vitec.com')
			// ->setFrom(array('noreply@vitec.com' => 'VITEC PO Manager'))
			->setFrom(array('noreply@vitec.com' => 'The VITEC team'))
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
			$email->attach(\Swift_Attachment::fromPath($this->files_root_path . $emailFields['attachedFile']));
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
		
		//echo getcwd();

		
		$this->mailer->send($email);
		
		$nowDate = new \DateTime('NOW');
		$log = $nowDate->format('Y-m-d H:i:s') . " ---EMAIL SENT TO " . $emailFields['sendTo'];
		// $log = "---EMAIL SENT TO " . $emailFields['sendTo'];
		// $log .= "\n---CC TO: " . $emailFields['ccTo'];
		$log .= "---CC TO: " . $emailFields['ccTo'];
		// $log .= "\n---BCC TO: " . $emailFields['bccTo'];
		$log .= "---BCC TO: " . $emailFields['bccTo'];
		// $log .= "\n---SUBJECT: " . $emailFields['subject'];
		$log .= "---SUBJECT: " . $emailFields['subject'];
		// $log .= "\n---MESSAGE: " . $emailFields['message'];
		//$log .= "---MESSAGE: " . $emailFields['message'];
		
		return $log;
	}

}