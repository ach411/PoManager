<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadElifesheetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('UploadElifesheet')
//            ->addArgument('shipmentItemId')
            ->setDescription('Generate and Upload elifesheet of a shipment item')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $em = $this->getContainer()->get('doctrine')->getManager();

        /* $shipmentItemId = $input->getArgument('shipmentItemId'); */

        /* $repositoryShipmentItem = $this->getContainer()->get('doctrine') */
		/*     ->getManager() */
		/*     ->getRepository('AchPoManagerBundle:ShipmentItem'); */

        //$shipmentItemInstance = $repositoryShipmentItem->find(intval($shipmentItemId));

        $repositoryUploadElifesheetPending = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:UploadElifesheetPending');

        $uploadElifesheetPendingInstances = $repositoryUploadElifesheetPending->findAll();

        foreach($uploadElifesheetPendingInstances as $uploadElifesheetPendingInstance) {
            $shipmentItemInstance = $uploadElifesheetPendingInstance->getShipmentItem();
            $log = "Processing elifesheet related to shipmentItem #" . $shipmentItemInstance->getId() . "\n";
            $log .= "Number of system e-lifesheet to upload: " . $shipmentItemInstance->getQty() . "\n";
            $log .= $this->getContainer()->get('ach_po_manager.upload_elifesheet')->uploadElifeSheet($shipmentItemInstance);

            // if error is detected during sync process then log into syslog and send email to webpage admin(s)
            if(stripos($log, "error") !== false) {
                $logger->error($log);
                $this->sendEmailAdmin($log, $output);
            }
            else {
                $em->remove($uploadElifesheetPendingInstance);
            }
            
            $output->writeln($log . "\n...end of upload\n\n");
        }

        $em->flush();


    }
    
    private function sendEmailAdmin($errorlog, $output)
    {
        $adminEmails = $this->getContainer()->getParameter('admin_emails');
        $fromEmails = $this->getContainer()->getParameter('from_emails');
        
        $message = \Swift_Message::newInstance()
        ->setSubject('Prod Database Sync Error')
        ->setFrom($fromEmails['core'])
        ->setTo($adminEmails)
        ->setBody(
            /* $this->renderView( */
            /*     'HelloBundle:Hello:email.txt.twig', */
            /*     array('name' => $name) */
            /* ) */
            "An error ocurred when PoManager attempted to create and upload the e-lifesheet\nSee following description:\n".$errorlog
        )
            ;

        try {
            $this->getContainer()->get('mailer')->send($message);
            
            $transport = $this->getContainer()->get('mailer')->getTransport();
            if (!$transport instanceof \Swift_Transport_SpoolTransport) {
                return;
            }
            
            $spool = $transport->getSpool();
            if (!$spool instanceof \Swift_MemorySpool) {
                return;
            }
            
            $spool->flushQueue($this->getContainer()->get('swiftmailer.transport.real'));
        }
        catch(\Exception $e) {
            $output->writeln('Error when sending email: '. $e->getMessage());
        }

    }
        
}