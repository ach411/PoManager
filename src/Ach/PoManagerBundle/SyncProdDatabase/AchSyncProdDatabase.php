<?php

namespace Ach\PoManagerBundle\SyncProdDatabase;

use Doctrine\ORM\EntityManager;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;


class AchSyncProdDatabase
{
	protected $em;
    protected $external_sql;
    protected $external_sql_host; 
	protected $external_sql_port; 
    protected $external_sql_db_name;
    protected $external_sql_user;
    protected $external_sql_pass;
    protected $lot;

    // query to remote production database string
    protected $external_sql_query = 'SELECT lot_num, lot_unit_count, System_SN as sn FROM Palettes INNER JOIN (SELECT Num_palette as lot_num, COUNT(System_SN) as lot_unit_count FROM Palettes WHERE Synchro_BDD_PO = 0 GROUP BY lot_num) as Counttab on Palettes.Num_palette = Counttab.lot_num WHERE Synchro_BDD_PO=0 ORDER BY lot_num ASC, sn';

    public function __construct(EntityManager $entityManager, $external_sql, $lot)
	{
		$this->em = $entityManager;
        // get the parameters to access the remote production database
        $this->external_sql_host    = $external_sql['host'];
        $this->external_sql_port    = $external_sql['port'];
		$this->external_sql_db_name = $external_sql['db_name'];
		$this->external_sql_user	= $external_sql['user'];
		$this->external_sql_pass	= $external_sql['pass'];
        $this->lot                  = $lot;

	}
	
	public function syncShipmentBatch($systemName)
	{
        // string variable to store the log
        $log = '';
        
        // convert systemName to upper case
        $systemName=strtoupper($systemName);

        // force it to SK38 for now: to be changed in future
        if($systemName != "SK38") {
            $log .= "Warning: only SK38 is supported for now... Synchro aborted\n";
            return $log;
        }
        // get the shipment batch repository
   		$repositoryShipmentBatch = $this->em->getRepository('AchPoManagerBundle:ShipmentBatch');
        $repositorySerialNumber = $this->em->getRepository('AchPoManagerBundle:SerialNumber');
                
        // get the production lot configuration: how many units per lot/pallet
        if(array_key_exists($systemName, $this->lot)) {
            $unit_per_lot = $this->lot[$systemName];
        }
        else {
            $log .= 'Error: System ' . $systemName . ' has not been set up' . "... Synchro Aborted\n";
            return $log;
        }

        // connect to the database
        try {
            $bdd = new \PDO('mysql:host='.$this->external_sql_host.';port='.$this->external_sql_port.';dbname='.$this->external_sql_db_name, $this->external_sql_user, $this->external_sql_pass);
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
                        $log .= 'Error: in production database, number of units in lot/pallet ' . $result['lot_num'] . ' is equal to ' . $result['lot_unit_count'] . ', it should be equal to ' . $unit_per_lot . "... Synchro Aborted\n";
                        return $log;
                    }
                }
                // display the count of S/N to be synchronized
                $log .= count($results) . ' S/N to be synchronized have been detected' . "\n";
                
                //2. Fill in the locale database
                $log .= 'Copying to PoManager Datebase...' . "\n";
                $tabBatch = array();
                $tabLotNum = array();
                $i = 0;
                foreach ($results as $result) {
                    if($i % $unit_per_lot == 0) {
                        $tabBatch[$i / $unit_per_lot] = new ShipmentBatch();
                        $tabBatch[$i / $unit_per_lot]->setNum($result['lot_num']);
                        $tabBatch[$i / $unit_per_lot]->setProductName($systemName);
                        $tabBatch[$i / $unit_per_lot]->setComment('auto-imported from the production database');
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
                    $tabBatch[$i / $unit_per_lot]->addSerialNumber($snInstance);
                    $this->em->persist($tabBatch[$i / $unit_per_lot]);
                    $i++;
                }
                $this->em->flush();
                
                //3. Mark the copied entries as being synchronized
                // prepare the query
                $tabLotNumString = implode(',',$tabLotNum);
                $this->external_sql_query = 'UPDATE Palettes SET Synchro_BDD_PO = 1 WHERE Num_palette IN (' . $tabLotNumString . ')';
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
