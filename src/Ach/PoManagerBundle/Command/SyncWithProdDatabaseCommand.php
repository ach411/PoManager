<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;

class SyncWithProdDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('SyncWithProdDatabase')
            ->addArgument('systemName')
            //->addArgument('host')
            //->addArgument('baseUrl')
            ->setDescription('Send notification email for event in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start synchro with production database...');

        // get the parameters to access the remote production database
        $external_sql_host = $this->getContainer()->getParameter('external_sql_host');
        $external_sql_port = $this->getContainer()->getParameter('external_sql_port');
        $external_sql_db_name = $this->getContainer()->getParameter('external_sql_db_name');
        $external_sql_user = $this->getContainer()->getParameter('external_sql_user');
        $external_sql_pass = $this->getContainer()->getParameter('external_sql_pass');

        // query to remote production database string
        $external_sql_query = 'SELECT lot_num, lot_unit_count, System_SN as sn FROM Palettes INNER JOIN (SELECT Num_palette as lot_num, COUNT(System_SN) as lot_unit_count FROM Palettes WHERE Synchro_BDD_PO = 0 GROUP BY lot_num) as Counttab on Palettes.Num_palette = Counttab.lot_num WHERE Synchro_BDD_PO=0 ORDER BY lot_num ASC, sn';


        // get the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        // get the shipment batch repository
   		$repositoryShipmentBatch = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:ShipmentBatch');

        // get the production lot configuration: how many units per lot/pallet
        if($this->getContainer()->hasParameter('lot_' . $input->getArgument('systemName'))) {
            $unit_per_lot =  $this->getContainer()->getParameter('lot_' . $input->getArgument('systemName'));
        }
        else {
            $output->writeln('Error: System ' . $input->getArgument('systemName') . ' has not been set up');
            return 3;
        }
        
         // connect to the database
        try {
            $bdd = new \PDO('mysql:host='.$external_sql_host.';port='.$external_sql_port.';dbname='.$external_sql_db_name, $external_sql_user, $external_sql_pass);
        }
        catch(Exception $e) {
            die('Erreur : '.$e->getMessage());
        }

        // prepare requests
        // $req = $bdd->prepare('SELECT Num_palette, System_SN FROM Palettes where Synchro_BDD_PO=0');

        //$req = $bdd->prepare('SELECT Num_palette as lot_num, COUNT(System_SN) as unit_count FROM Palettes WHERE Synchro_BDD_PO = 0 GROUP BY Num_palette');

        $req = $bdd->prepare($external_sql_query);

        // execute the request
        if($req->execute()) {
            // fetch the result
            $results = $req->fetchall();
            // close the request
            $req->closeCursor();

            //1. check that the number of units per lot/pallet is correct 
            foreach ($results as $result) {
                $output->writeln($result['lot_num'] . ' - ' . $result['lot_unit_count'] . ' - ' . $result['sn']);
                if ($result['lot_unit_count'] != $unit_per_lot) {
                    $output->writeln('Error: in production database, number of units in lot/pallet ' . $result['lot_num'] . ' is equal to ' . $result['lot_unit_count'] . ', it should be equal to ' . $unit_per_lot );
                    return 4;
                }
            }

            // display the count of S/N to be synchronized
            $output->writeln(count($results) . ' S/N to be synchronized are detected...');

            $tabBatch = array();
            $i = 0;
            foreach ($results as $result) {
                if($i % $unit_per_lot == 0) {
                    $tabBatch[$i / $unit_per_lot] = new ShipmentBatch();
                    $tabBatch[$i / $unit_per_lot]->setNum($result['lot_num']);
                    $tabBatch[$i / $unit_per_lot]->setProductName($input->getArgument('systemName'));
                    $tabBatch[$i / $unit_per_lot]->setComment('auto-imported from the production database');
                }
                $snInstance = new SerialNumber($result['sn'], null,'auto-imported from production database');
                $em->persist($snInstance);
                $tabBatch[$i / $unit_per_lot]->addSerialNumber($snInstance);
                $em->persist($tabBatch[$i / $unit_per_lot]);
                $i++;
            }

            $em->flush();

            $output->writeln('End of Synchro');
            

            /*
            foreach ($results as $result) {
                $output->writeln($result['lot_num'] . ' - ' . $result['unit_count']);
                if($result['unit_count'] != $unit_per_lot) {
                    $output->writeln('Error: in production database, number of units in lot/pallet ' . $result['lot_num'] . ' is equal to ' . $result['unit_count'] . ', it should be equal to ' . $unit_per_lot );
                    return 4;
                }
                else {
                    $req = $bdd->prepare('SELECT System_SN as sn FROM Palettes WHERE Synchro_BDD_PO = 0 AND Num_palette = :lot_num');
                    if($req->execute(array(':lot_num' => $result['lot_num']))) {

                        
                        addSerialNumber
                        
                    }
                }
            }
            */
        }

    }
}