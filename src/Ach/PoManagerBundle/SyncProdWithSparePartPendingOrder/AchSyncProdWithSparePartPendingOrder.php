<?php

namespace Ach\PoManagerBundle\SyncProdWithSparePartPendingOrder;

use Doctrine\ORM\EntityManager;
use Ach\PoManagerBundle\ConnectProdDatabase\AchConnectProdDatabase;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;


class AchSyncProdWithSparePartPendingOrder
{
	protected $em;
    protected $connectProdDB;

    // query to remote production database string
    // protected $external_sql_query = 'SELECT lot_num, lot_unit_count, System_SN as sn FROM Palettes INNER JOIN (SELECT Num_palette as lot_num, COUNT(System_SN) as lot_unit_count FROM Palettes WHERE Synchro_BDD_PO = 0 GROUP BY lot_num) as Counttab on Palettes.Num_palette = Counttab.lot_num WHERE Synchro_BDD_PO=0 ORDER BY lot_num ASC, sn';
    protected $external_sql_query = 'SELECT * FROM SK_Spares_PoItem';

    public function __construct(EntityManager $entityManager, AchConnectProdDatabase $connectProdDB)
	{
		$this->em = $entityManager;
        $this->connectProdDB        = $connectProdDB;

	}
	
	public function syncSpareParts()
	{
        // string variable to store the log
        $log = '';
        
        // get the shipment batch repository
   		$repositoryPoItem = $this->em->getRepository('AchPoManagerBundle:PoItem');

        // find all the pending spare parts order with shipping manager id 1... (to be improved in the future)
        $poItemInstances = $repositoryPoItem->findPoItemByStatusShippingManagerCategory(1, "APPROVED", "PARTIALLY SHIPPED", "Spare Part");
        //$poItemInstances = $repositoryPoItem->findPoItemByStatusShippingManager(1, "APPROVED", "PARTIALLY SHIPPED");

        if(empty($poItemInstances))
            $log .= "No spare parts pending order found in PoManager\n";
        else
            $log .= "Here is a list of spare parts found in PoManager:\n";

        
        foreach ($poItemInstances as $poItemInstance) {
            $log .= 'id=' . $poItemInstance->getId() . ' - PO Num=' . $poItemInstance->getPo()->getNum() . ' - ' . $poItemInstance->getRevision()->getProduct()->getPn() . ' - ' . $poItemInstance->getDescription() . ' - Qty=' .  $poItemInstance->getQty() . "\n" ;
        }

        $log .= "================================================\n";

        // connect to the database
        try {
            $db = $this->connectProdDB->getPDO();
        }
        catch(\Exception $e) {
            $log .= 'Error: '.$e->getMessage()."\n";
            return $log;
        }

        $req = $db->prepare($this->external_sql_query);

        // execute the request
        if($req->execute()) {
            // fetch the result
            $results = $req->fetchall();

            // close the request
            $req->closeCursor();

            // if there is something to synchronize, then continue
            if (!empty($results)) {
                $log .= "Here is a list of pending spare parts order in the Prod Database:\n";
                
                foreach ($results as $result) {
                    $log .= 'id=' . $result['id'] . ' - PO num =' . $result['PO_Num'] . "\n";
                    
                }
                
            }
            else {
                $log .= "No spare parts pending order found in the prod database\n";
            }

            $log .= "================================================\n";

            $to_add= array();
            $to_modify= array();
            $to_keep= array();
            $to_remove= array();

            // find row to add, modify and keep
            foreach ($poItemInstances as $poItemInstance) {
                $nodetect = true;
                $qtymodified = false;
                foreach ($results as $result) {
                    if ($result['id'] == $poItemInstance->getId()) {
                        $nodetect = false;
                        $log .= "Detected: # ProdDB: " . $result['id'] . " - PoManager: " . $poItemInstance->getId() . "\n";
                        if($result['Qty_due'] != $poItemInstance->getQty() or $result['Due_date_SK'] != $poItemInstance->getdueDate()->format("Y-m-d") ) {
                            $qtymodified = true;
                        }
                    }
                }
                if($nodetect) {
                    $to_add[] = $poItemInstance;
                    $log .= "To add (ID): " . $poItemInstance->getId() . "\n";
                }
                elseif($qtymodified) {
                    $to_modify[] = $poItemInstance;
                    $log .= "To modify (ID): " . $poItemInstance->getId() . "\n";
                }
                else {
                    $to_keep[] = $poItemInstance->getId();
                    $log .= "To keep (ID): " . $poItemInstance->getId() . "\n";
                }
            }

            //$log .= var_dump($to_keep);
            
            // find row to delete
            foreach ($results as $result) {
                $nodetect = true;
                foreach ($to_keep as $keep) {
                    if($keep == $result['id'])
                        $nodetect = false;
                }
                foreach ($to_modify as $modify) {
                    if($modify->getId() == $result['id'])
                        $nodetect = false;
                }
                if($nodetect) {
                    $to_remove[] = $result['id'];
                    $log .= "To remove (ID): " . $result['id'] . "\n";
                }
            }

            /* $add_query = "INSERT INTO SK_Spares_PoItem(id, PO_Num, Release_Num, VITEC_index, Due_date_SK, Qty_due) VALUES (:id, :PO_Num, :Release_Num, :VITEC_index, :Due_date_SK, :Qty_due)"; */
            /* $modify_query = "UPDATE SK_Spares_PoItem SET Due_date_SK=:Due_date_SK, Qty_due=:Qty_due WHERE id=:id"; */
            /* $remove_query = "DELETE FROM SK_Spares_PoItem WHERE id=:id"; */

            //process the add
            foreach ($to_add as $add) {
                
                $id = $add->getId();
                $PO_Num = $add->getPo()->getNum();
                $Release_Num = $add->getPo()->getRelNum();
                $VITEC_index = $add->getRevision()->getProduct()->getPn();
                $Due_date_SK = $add->getDueDate()->format('Y-m-d');
                $Qty_due = $add->getQty();
                $Description = $add->getDescription();

                $add_query = "INSERT INTO SK_Spares_PoItem(id, PO_Num, Release_Num, VITEC_index, Due_date_SK, Qty_due, Description) VALUES ( ?, ?, ?, ?, ?, ?, ?)";
                
                $req = $db->prepare($add_query);
                
                $req->execute(array($id, $PO_Num, $Release_Num, $VITEC_index, $Due_date_SK, $Qty_due, $Description));

                $log .= "id: " . $id . ", PO_num: " . $Release_Num . ", VITEC_index: " . $VITEC_index . " was added to the Prod Database pending order\n";
                
                $req->closeCursor();
            }
            
            // process the modification
            foreach ($to_modify as $modify) {

                $id = $modify->getId();
                $Due_date_SK = $modify->getDueDate()->format('Y-m-d');
                $Qty_due = $modify->getQty();
                
                $modify_query = "UPDATE SK_Spares_PoItem SET Due_date_SK=?, Qty_due=? WHERE id=?";

                $req = $db->prepare($modify_query);

                $req->execute(array($Due_date_SK, $Qty_due, $id));

                $log .= "id: " . $id . " was modified\n";

                $req->closeCursor();
            }

            // process the delete
            foreach ($to_remove as $remove) {

                $remove_query = "DELETE FROM SK_Spares_PoItem WHERE id=?" ;

                $req = $db->prepare($remove_query);
                
                $req->execute(array($remove));

                $log .= "id: " . $remove . " was removed from the Prod Database pending order\n";

                $req->closeCursor();
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
