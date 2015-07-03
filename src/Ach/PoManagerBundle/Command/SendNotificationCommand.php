<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendNotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('SendNotification')
	    ->addArgument('host')
	    ->addArgument('baseUrl')
            ->setDescription('Send notification email for event in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $this->getContainer()->get('router')->getContext();
        $context->setHost($input->getArgument('host'));
        $context->setScheme('http');
        $context->setBaseUrl($input->getArgument('baseUrl'));
		
		$repositoryNotification = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Notification');
		
		$listNotifications = $repositoryNotification->findAll();
		
		$em = $this->getContainer()->get('doctrine')->getManager();
		
		$log = null;
		
		// scan all the pending notification of the table and send message for each
		foreach($listNotifications as $notification)
		{
			$log = $log.$this->getContainer()->get('ach_po_manager.send_notification')->sendNotification($notification);
			$output->writeln('SendNotification launched');
			$em->remove($notification);
		}
		
		$transport = $this->getContainer()->get('mailer')->getTransport();
		if (!$transport instanceof \Swift_Transport_SpoolTransport) {
			return;
		}
		
		$spool = $transport->getSpool();
			if (!$spool instanceof \Swift_MemorySpool) {
			return;
		}
		
		$spool->flushQueue($this->getContainer()->get('swiftmailer.transport.real'));	
		
		$em->flush();
		
		$output->writeln('SendNotification executed: ' . $log);
    }
}