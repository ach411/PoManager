<?php

namespace Ach\PoManagerBundle\UploadElifesheet;

use Doctrine\ORM\EntityManager;
use Ach\PoManagerBundle\ConnectProdDatabase\AchConnectProdDatabase;
use Ach\PoManagerBundle\Entity\ShipmentBatch;
use Ach\PoManagerBundle\Entity\SerialNumber;


class AchUploadElifesheet
{
	protected $em;
    protected $connectProdDB;
    protected $ftp_server;
	protected $ftp_user_name; 
	protected $ftp_user_pass; 
	protected $ftp_temp_file;
    protected $ftp_remote_path;
    protected $elifesheet_files_path;

    // query to remote production database string
    protected $sql_query_pattern = 'SELECT System_SN, Assembly_date, PSU_SN, Motherboard_SN, SK38_M_SN, LCD_SN, DDR1_SN, DDR2_SN, MACID1_MB, MACID2_MB, HDD_SN FROM SK38 WHERE System_SN like ';

    public function __construct(EntityManager $entityManager, AchConnectProdDatabase $connectProdDB, $ftp_parameters, $elifesheet_files_path)
	{
		$this->em                   = $entityManager;
        $this->connectProdDB        = $connectProdDB;
        $this->ftp_server           = $ftp_parameters['server']; 
        $this->ftp_user_name        = $ftp_parameters['user_name']; 
        $this->ftp_user_pass        = $ftp_parameters['user_pass']; 
        $this->ftp_temp_file        = $ftp_parameters['temp_file'];
        $this->ftp_remote_path      = $ftp_parameters['remote_path'];
        $this->elifesheet_files_path = $elifesheet_files_path;
	}
	
	public function UploadElifesheet($shipmentItemInstance)
	{
        
        // string variable to store the log
        $log = '';

        $shipmentBatchInstances = $shipmentItemInstance->getShipmentBatches();
        
        $snArray = array();

        foreach($shipmentBatchInstances as $shipmentBatchInstance) {

            // force it to SK38 for now: to be changed in future
            if($shipmentBatchInstance->getProductName() != "SK38") {
                $log .= "Error: only SK38 is supported for now... Cannot Upload E-lifesheet\n";
                return $log;
            }
            
            $serialNumberInstances = $shipmentBatchInstance->getSerialNumbers();
            foreach($serialNumberInstances as $sn) {
                $snArray[] = "'" . $sn->getSerialNumber() . "'";
                //$log.= "'" . $sn->getSerialNumber() . "'";
            }
        }

        //$this->external_sql_query .= implode(" OR System_SN like ",$snArray) . " ORDER BY id ASC;";
        $sql_query = $this->sql_query_pattern . implode(" OR System_SN like ",$snArray) . " ORDER BY id ASC;";

        //$log .= $sql_query;

        // connect to the database
        try {
            $bdd = $this->connectProdDB->getPDO();
        }
        catch(\Exception $e) {
            $log .= 'Error: '.$e->getMessage()."\n";
            return $log;
        }

        $req = $bdd->prepare($sql_query);

        // execute the request
        if($req->execute()) {
            // fetch the result
            $results = $req->fetchall();

            // close the request
            $req->closeCursor();

            // if the number of rows returned is correct then continue
            if (count($results) == $shipmentItemInstance->getQty()) {

                //$log .= var_dump($results);
                //$log .= "\n" . getcwd() . "\n";

                // connect to FTP site
                $conn_id = ftp_connect($this->ftp_server);
                if (! @ftp_login($conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {
                    $log .= "Error: cannot connect to FTP site with current credentials... Upload aborted" ;
                    return $log;
                }
                
                foreach($results as $result) {
                    // check if it is really SK38
                    if(preg_match("/SK38-SYS (.*)/", $result['System_SN'], $snRegex) !== 1 ) {

                        // close the connection 
                        ftp_close($conn_id);
                        
                        $log .= "Error: System S/N does not match SK38... Upload aborted\n";
                        return $log;
                    }

                    // write elifesheet in temp file
                    $tempFileHandle = fopen($this->ftp_temp_file,"w");
                    // write file
                    fwrite($tempFileHandle, "System SK38 S/N;" . $result['System_SN']     . ";\n" );
                    fwrite($tempFileHandle, "Assembly date;"   . $result['Assembly_date'] . ";\n" );
                    fwrite($tempFileHandle, "PSU S/N;"         . $result['PSU_SN']        . ";\n" );
                    fwrite($tempFileHandle, "Motherboard S/N;" . $result['PSU_SN']        . ";\n" );
                    fwrite($tempFileHandle, "MAC ADDR 1;"      . $result['MACID1_MB']     . ";\n" );
                    fwrite($tempFileHandle, "MAC ADDR 2;"      . $result['MACID2_MB']     . ";\n" );
                    fwrite($tempFileHandle, "LCD S/N;"         . $result['LCD_SN']        . ";\n" );
                    fwrite($tempFileHandle, "HDD S/N;"         . $result['HDD_SN']        . ";\n" );
                    fwrite($tempFileHandle, "SK38-M S/N;"      . $result['SK38_M_SN']     . ";\n" );
                    fwrite($tempFileHandle, "DDR1 S/N;"        . $result['DDR1_SN']       . ";\n" );
                    fwrite($tempFileHandle, "DDR2 S/N;"        . $result['DDR2_SN']       . ";\n" );
                    fclose($tempFileHandle);

                    //push file onto FTP site
                    $remote_file = $this->ftp_remote_path . $snRegex[1] . ".csv";
                    if (ftp_put($conn_id, $remote_file, $this->ftp_temp_file, FTP_ASCII)) {
                        $log .= "successfully uploaded $remote_file\n";
                    } else {
                        $log .= "Error: There was a problem while uploading $remote_file\n";
                    }

                    copy($this->ftp_temp_file, ".." . $this->elifesheet_files_path . "/SK38-SYS_" . $snRegex[1] . ".csv");

                }
                
                // close the connection 
                ftp_close($conn_id);

                // delete tmp file
                unlink($this->ftp_temp_file);
                
            }
            else {
                $log .= "Error: prod database query does not return right number of systems (" . count($results) . ")\n" . $sql_query . "\n";
                return $log;
                
            }
        }
        else {
            $req->closeCursor();
            $log .= "Error: Fail to execute query on prod database... Upload Aborted\n";
            return $log;
        }
            
        return $log;
        
	}

}
