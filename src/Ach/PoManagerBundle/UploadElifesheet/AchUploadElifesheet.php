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
	protected $ftp_temp_file1;
	protected $ftp_temp_file2;
    protected $ftp_remote_path;

    // query to remote production database string
    protected $sql_query_pattern = 'SELECT System_SN, Assembly_date, PSU_SN, Motherboard_SN, SK38_M_SN, LCD_SN, DDR1_SN, DDR2_SN, MACID1_MB, MACID2_MB, HDD_SN, SATADOM_SN, CARD_USB3_SN FROM SK38 WHERE System_SN like ';

    public function __construct(EntityManager $entityManager, AchConnectProdDatabase $connectProdDB, $ftp_parameters)
	{
		$this->em                   = $entityManager;
        $this->connectProdDB        = $connectProdDB;
        $this->ftp_server           = $ftp_parameters['server']; 
        $this->ftp_user_name        = $ftp_parameters['user_name']; 
        $this->ftp_user_pass        = $ftp_parameters['user_pass']; 
        $this->ftp_temp_file1       = $ftp_parameters['temp_file1'];
        $this->ftp_temp_file2       = $ftp_parameters['temp_file2'];
        $this->ftp_remote_path      = $ftp_parameters['remote_path'];;
	}
	

	public function UploadElifesheet($shipmentItemInstance)
	{
        
	    // string variable to store the log
            $log = '';

	    $shipmentBatchInstances = $shipmentItemInstance->getShipmentBatches();
        
            foreach($shipmentBatchInstances as $shipmentBatchInstance) {
	    
		// force it to SK38 for now: to be changed in future
            	if($shipmentBatchInstance->getProductName() != "SK38") {
                    $log .= "Error: only SK38 is supported for now... Cannot Upload E-lifesheet\n";
                    return $log;
                }
            
		$serialNumberInstances = $shipmentBatchInstance->getSerialNumbers();
		
		$snArray = array();
	    
            	foreach($serialNumberInstances as $sn) {
                    $snArray[] = "'" . $sn->getSerialNumber() . "'";
                    $sql_query = $this->sql_query_pattern . implode(" OR System_SN like ",$snArray) . " ORDER BY id ASC;";
		}

	        try {
		    // connect to the database
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

		    //$log .= "count result= " . count($results) . "\n";
		    //$log .= "count serial number instance= " . count($serialNumberInstances) . "\n";

                    // if the number of rows returned is correct then continue
                    if (count($results) == count($serialNumberInstances)) {
		    
                        //$log .= var_dump($results);

                        // connect to FTP site
                	$conn_id = ftp_connect($this->ftp_server);
                	if (! @ftp_login($conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {
                    	    $log .= "Error: cannot connect to FTP site with current credentials... Upload aborted" ;
                    	    return $log;
                	}

			$tempFile2Handle = fopen($this->ftp_temp_file2,"w");;

			fwrite($tempFile2Handle, "Assembly date,Manufacturing PN, System S/N,Motherboard S/N,SK38-M S/N,DDR1 S/N,DDR2 S/N,PSU S/N,LCD S/N,MAC ADDR 1,MAC ADDR 2,HDD S/N,SATADOM S/N,CARD USB3 S/N\n");

			// get the P/n and the revision
			$revisionInstance = $shipmentBatchInstance->getShipmentItem()->getPoItem()->getRevision();
			$pn = $revisionInstance->getProduct()->getPn();
                
			foreach($results as $result) {
                    	    // check if it is really SK38
                    	    if(preg_match("/SK38-SYS (.*)/", $result['System_SN'], $snRegex) !== 1 )
			    {
			    
                                // close the connection 
                                ftp_close($conn_id);
                            	
                                $log .= "Error: System S/N does not match SK38... Upload aborted\n";
                                return $log;
                    	    }
			    
			    // write elifesheet in temp file
                    	    $tempFile1Handle = fopen($this->ftp_temp_file1,"w");
                    	    // write file
                    	    // fwrite($tempFileHandle, "System SK38 S/N;" . $result['System_SN']     . ";\n" );
                    	    // fwrite($tempFileHandle, "Assembly date;"   . $result['Assembly_date'] . ";\n" );
                    	    // fwrite($tempFileHandle, "PSU S/N;"         . $result['PSU_SN']        . ";\n" );
                    	    // fwrite($tempFileHandle, "Motherboard S/N;" . $result['PSU_SN']        . ";\n" );
                    	    // fwrite($tempFileHandle, "MAC ADDR 1;"      . $result['MACID1_MB']     . ";\n" );
                    	    // fwrite($tempFileHandle, "MAC ADDR 2;"      . $result['MACID2_MB']     . ";\n" );
                    	    // fwrite($tempFileHandle, "LCD S/N;"         . $result['LCD_SN']        . ";\n" );
                    	    // fwrite($tempFileHandle, "HDD S/N;"         . $result['HDD_SN']        . ";\n" );
                    	    // fwrite($tempFileHandle, "SK38-M S/N;"      . $result['SK38_M_SN']     . ";\n" );
                    	    // fwrite($tempFileHandle, "DDR1 S/N;"        . $result['DDR1_SN']       . ";\n" );
                    	    // fwrite($tempFileHandle, "DDR2 S/N;"        . $result['DDR2_SN']       . ";\n" );
		    	    fwrite($tempFile1Handle, "Assembly date,System S/N,Motherboard S/N,SK38-M S/N,DDR1 S/N,DDR2 S/N,PSU S/N,LCD S/N,MAC ADDR 1,MAC ADDR 2,HDD S/N,SATADOM S/N,CARD USB3 S/N\n");
		    	    fwrite($tempFile1Handle, $result['Assembly_date'] . "," . $result['System_SN'] . "," . $result['Motherboard_SN'] . "," . $result['SK38_M_SN'] . "," . $result['DDR1_SN'] . "," . $result['DDR2_SN'] . "," . $result['PSU_SN'] . "," . $result['LCD_SN'] . "," . $result['MACID1_MB'] . "," . $result['MACID2_MB'] . "," . $result['HDD_SN'] . "," . $result['SATADOM_SN'] . "," . $result['CARD_USB3_SN'] . "\n");
			    fwrite($tempFile2Handle, $result['Assembly_date'] . "," . $pn . " Rev " . $revisionInstance->getRevisionCust() . "," . $result['System_SN'] . "," . $result['Motherboard_SN'] . "," . $result['SK38_M_SN'] . "," . $result['DDR1_SN'] . "," . $result['DDR2_SN'] . "," . $result['PSU_SN'] . "," . $result['LCD_SN'] . "," . $result['MACID1_MB'] . "," . $result['MACID2_MB'] . "," . $result['HDD_SN'] . "," . $result['SATADOM_SN'] . "," . $result['CARD_USB3_SN'] . "\n");
			    fclose($tempFile1Handle);

                   	    //push unit file onto FTP site
                    	    $remote_file = $this->ftp_remote_path . $snRegex[1] . ".csv";
                    	    if (ftp_put($conn_id, $remote_file, $this->ftp_temp_file1, FTP_ASCII))
			    {
                                $log .= "successfully uploaded $remote_file\n";
			    }
			    else
			    {
				$log .= "Error: There was a problem while uploading $remote_file\n";
                    	    }

			    // delete tmp file
                	    unlink($this->ftp_temp_file1);
			    
                    	}

			fclose($tempFile2Handle);

			//push unit file onto FTP site
                    	$remote_file = $this->ftp_remote_path . "P32795_LifeSheet_Batch_" . $shipmentBatchInstance->getNum() . ".csv";
                    	if (ftp_put($conn_id, $remote_file, $this->ftp_temp_file2, FTP_ASCII))
			{
                            $log .= "successfully uploaded $remote_file\n";
			}
			else
			{
			    $log .= "Error: There was a problem while uploading $remote_file\n";
                    	}
			
			// delete tmp file
			unlink($this->ftp_temp_file2);
                	
		    	// close the connection 
                    	ftp_close($conn_id);
			                
                    }
		    else
		    {
                        $log .= "Error: prod database query does not return right number of systems (" . count($results) . ")\n" . $sql_query . "\n";
                        return $log;
            	    }
        	}
        	else
		{
		    $req->closeCursor();
                    $log .= "Error: Fail to execute query on prod database... Upload Aborted\n";
            	    return $log;
        	}

            }
	    return $log;
        
	}

}
