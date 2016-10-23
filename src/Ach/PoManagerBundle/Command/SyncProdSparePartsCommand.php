<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProdSparePartsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('SyncProdSpareParts')
            //->addArgument('systemName')
            ->setDescription('Synchronize Production Database with PoManager pending spare part orders')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');

        $output->writeln('Start synchro with spare parts production database...');

        $log = $this->getContainer()->get('ach_po_manager.sync_prod_spares')->syncSpareParts();

        // if error is detected during sync process then log into syslog and send email to webpage admin(s)
        if(stripos($log, "error") !== false) {
            $logger->error($log);
            $this->sendEmailAdmin($log, $output);
        }

        $output->writeln($log . "end of synchro");

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
