<?php

namespace Ach\PoManagerBundle\SyncProdDatabase;

use Doctrine\ORM\EntityManager;
use Ach\PoManagerBundle\ConnectProdDatabase\AchConnectProdDatabase;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;


class AchSyncProdDatabase
{
	protected $em;
    /* protected $external_sql; */
    /* protected $external_sql_host;  */
	/* protected $external_sql_port;  */
    /* protected $external_sql_db_name; */
    /* protected $external_sql_user; */
    /* protected $external_sql_pass; */
    protected $lot;
    protected $connectProdDB;
    protected $external_select_query;
    protected $external_update_query;

    // query to remote production database string
    protected $external_sql_query; // = 'SELECT lot_num, lot_unit_count, System_SN as sn FROM Palettes INNER JOIN (SELECT Num_palette as lot_num, COUNT(System_SN) as lot_unit_count FROM Palettes WHERE Synchro_BDD_PO = 0 GROUP BY lot_num) as Counttab on Palettes.Num_palette = Counttab.lot_num WHERE Synchro_BDD_PO=0 ORDER BY lot_num ASC, sn';

    public function __construct(EntityManager $entityManager, AchConnectProdDatabase $connectProdDB, $lot, $external_select_query, $external_update_query)
	{
		$this->em = $entityManager;
        // get the parameters to access the remote production database
        /* $this->external_sql_host    = $external_sql['host']; */
        /* $this->external_sql_port    = $external_sql['port']; */
		/* $this->external_sql_db_name = $external_sql['db_name']; */
		/* $this->external_sql_user	= $external_sql['user']; */
		/* $this->external_sql_pass	= $external_sql['pass']; */
        $this->lot                  = $lot;
        $this->connectProdDB        = $connectProdDB;
        $this->external_select_query = $external_select_query;
        $this->external_update_query = $external_update_query;

	}
	
	public function syncShipmentBatch($systemName)
	{
        // string variable to store the log
        $log = '';
        
        // convert systemName to upper case
        $systemName=strtoupper($systemName);

        // force it to SK38 for now: to be changed in future
        /* if($systemName != "SK38") {
            $log .= "Warning: only SK38 is supported for now... Synchro aborted\n";
            return $log;
        } */
        // get the shipment batch repository
   		$repositoryShipmentBatch = $this->em->getRepository('AchPoManagerBundle:ShipmentBatch');
        $repositorySerialNumber = $this->em->getRepository('AchPoManagerBundle:SerialNumber');
                
        // get the production lot configuration: how many units per lot/pallet
        if(array_key_exists($systemName, $this->lot) and array_key_exists($systemName, $this->external_select_query)) {
            $unit_per_lot = $this->lot[$systemName];
            $this->external_sql_query = $this->external_select_query[$systemName];
            $log .= $this->external_sql_query . "\n";
        }
        else {
            $log .= 'Error: System ' . $systemName . ' has not been set up' . "... Synchro Aborted\n";
            return $log;
        }

        // connect to the database
        try {
            $bdd = $this->connectProdDB->getPDO();
        }
        catch(\Exception $e) {
            $log .= 'Error: '.$e->getMessage()."\n";
            return $log;
        }

        $req = $bdd->prepare($this->external_sql_query);

        // execute the request
        if($req->execute()) {
            // fetch the result
            $results = $req->fetchall();

            // close the request
            $req->closeCursor();

            // if there is something to synchronize, then continue
            if (!empty($results)) {
            
                //1. check that the number of units per lot/pallet is correct
                foreach ($results as $result) {
                    $log .= $result['lot_num'] . ' - ' . $result['sn'] . "\n";
                    if ($result['lot_unit_count'] != $unit_per_lot) {
                        $log .= 'Warning: in production database, number of units in lot/pallet ' . $result['lot_num'] . ' is equal to ' . $result['lot_unit_count'] . ', it should normally be equal to ' . $unit_per_lot . "...\n";
                        //return $log;
                    }
                }
                // display the count of S/N to be synchronized
                $log .= count($results) . ' S/N to be synchronized have been detected' . "\n";
                
                //2. Fill in the locale database
                $log .= 'Copying to PoManager Datebase...' . "\n";
                $tabBatch = array();
                $tabLotNum = array();
                $i = 0; // index of the for loop
		$j = -1; // index of the batch table
		$k = 0; // contains value of i when new batch entry is needed
                foreach ($results as $result) {
                    if($i == $k) {
		    	$k = $i + $result['lot_unit_count'];
			$j++;
                        $tabBatch[$j] = new ShipmentBatch();
                        $tabBatch[$j]->setNum($result['lot_num']);
                        $tabBatch[$j]->setProductName($systemName);
                        $tabBatch[$j]->setComment('auto-imported from the production database v1.1');
                        $tabLotNum[] = strval($result['lot_num']);
                    }
                    // check if S/N already exists in the database
		    $sameSn = $repositorySerialNumber->findBySerialNumber($result['sn'], true);
                    if(!empty($sameSn)) {
                        $log .= 'Error: S/N ' . $result['sn'] . " already exists in the PO database... Synchro Aborted\n";
                        return $log;
                    }
                    $snInstance = new SerialNumber($result['sn'], null,'auto-imported from production database');
                    $this->em->persist($snInstance);
                    $tabBatch[$j]->addSerialNumber($snInstance);
                    $this->em->persist($tabBatch[$j]);
                    $i++;
                }
                $this->em->flush();
                
                //3. Mark the copied entries as being synchronized
                // prepare the query
                $tabLotNumString = implode(',',$tabLotNum);
                // $this->external_sql_query = 'UPDATE Palettes SET Synchro_BDD_PO = 1 WHERE Num_palette IN (' . $tabLotNumString . ')';
		$this->external_sql_query = $this->external_update_query[$systemName] . $tabLotNumString . ')';
                $req2 = $bdd->prepare($this->external_sql_query);
                
                // execute the request
                if($req2->execute()) {
                    $log .= 'Mark prod database as synchronized' . "\n";
                }
                else {
                    $req2->closeCursor();
                    $log .= "Error: Fail to mark prod database as synchronized... Synchro Aborted\n";
                    return $log;
                }
                // close the request
                $req2->closeCursor();
            }
            else {
                $log .= "Nothing to synchronize\n";
                return $log;
            }

        }
        else {
            $req->closeCursor();
            $log .= "Error: Fail to execute query on prod database... Synchro Aborted\n";
            return $log;
        }

        return $log;
        
	}

}
