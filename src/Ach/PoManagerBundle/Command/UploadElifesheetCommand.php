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
            ->addArgument('shipmentItemId')
            ->setDescription('Generate and Upload elifesheet of a shipment item')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shipmentItemId = $input->getArgument('shipmentItemId');

        $repositoryShipmentItem = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:ShipmentItem');

        $shipmentItemInstance = $repositoryShipmentItem->find(intval($shipmentItemId));

        $output->writeln("Number of system elifesheet to upload:" . $shipmentItemInstance->getQty());

        $log = $this->getContainer()->get('ach_po_manager.upload_elifesheet')->uploadElifeSheet($shipmentItemInstance);

        // if error is detected during sync process then log into syslog and send email to webpage admin(s)
        if(stripos($log, "error") !== false) {
            $logger->error($log);
            $this->sendEmailAdmin($log, $output);
        }

        $output->writeln($log . "end of upload");

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
            "An error ocurred when PoManager attempted to sync with the Prod Database\nSee following description:\n".$errorlog
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