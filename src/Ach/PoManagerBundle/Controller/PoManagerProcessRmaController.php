<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Ach\PoManagerBundle\Entity\Rma;
use Ach\PoManagerBundle\Form\RmaType;
use Ach\PoManagerBundle\Form\RmaReceiveType;
use Ach\PoManagerBundle\Form\RmaRetrieveType;
use Ach\PoManagerBundle\Form\RmaUpdateType;

class PoManagerProcessRmaController extends Controller
{

    /* Manage the PO Item with the product manager */
    public function createRmaAction()
    {
        $rmaInstance = new Rma($this->get('kernel')->getRootDir() . '/../..' . $this->container->getParameter('rma_files_path'));
        $formRma = $this->createForm(new RmaType, $rmaInstance);

        $request = $this->get('request');
        
        if($request->getMethod() == 'POST') {
            $formRma->bind($request);
            if ($formRma->isValid()) {

                $em = $this->getDoctrine()->getManager();

                //check if no other RMA is open for the same unit
                $repoRma = $this->getDoctrine()->getRepository('AchPoManagerBundle:Rma');
                $rmaDouble = $repoRma->findOpenedBySn($rmaInstance->getSerialNumF());
                if(!empty($rmaDouble)) {
                    $message = "RMA is already currently opened for this S/N, check RMA number " . $rmaDouble->getNum();
                    return $this->render('AchPoManagerBundle:PoManager:error.html.twig', array('message' => $message, 'returnPath' => 'ach_po_manager_process_rma_create'));
                }

                // link rma to serial number concerned
                $repoSerialNumber = $this->getDoctrine()->getRepository('AchPoManagerBundle:SerialNumber');
                $serialNumInstance = $repoSerialNumber->findOneBySerialNumber($rmaInstance->getSerialNumF());
                if(is_null($serialNumInstance)) {
                    $message = 'S/N entry does not match any recorded units. Please make sure you enter complete serial number. Example: IP1-SYS D1515050';
                    return $this->render('AchPoManagerBundle:PoManager:error.html.twig', array('message' => $message, 'returnPath' => 'ach_po_manager_process_rma_create'));
                }
                $rmaInstance->setSerialNum($serialNumInstance);

                //auto generate the RMA number
                $currentDate = new \DateTime();
                $rmaInstance->setNum(strtoupper(substr($rmaInstance->getRepairLocation()->getName(),0,3)).$currentDate->format('ymd').str_pad($serialNumInstance->getId(), 8, '0', STR_PAD_LEFT));

                // set status of repair to waiting for reception
                $repoRepairStatus = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairStatus');
                $repairStatusInstance = $repoRepairStatus->findOneByName('Waiting_for_Reception');
                $rmaInstance->setRepairStatus($repairStatusInstance);
                
                //determination of warranty expiration
                //first make sure there's a record for the shipment
                if(is_null($serialNumInstance->getShipmentBatch()))
                    return new Response("No shipping batch seem to be recorded for this S/N");
                if(is_null($serialNumInstance->getShipmentBatch()->getShipmentItem()))
                    return new Response("S/N does not seem to have shipped yet");
                        
                $em->persist($rmaInstance);
                $em->flush();
                
                if(is_null($serialNumInstance->getShipmentBatch()->getShipmentItem()->getShipment()->getShippingDate() ))
                    $message = "RMA recorded. Warranty Status unknown since shipping date is undetermined";
                    //return new Response("RMA recorded. Warranty Status unknown since shipping date is undetermined");
                else {
                    $warrantyExpiration = $serialNumInstance->getShipmentBatch()
                                                            ->getShipmentItem()
                                                            ->getShipment()
                                                            ->getShippingDate()
                                                            ->add(new \DateInterval('P1Y'));
                    if ($currentDate > $warrantyExpiration )
                        $message = "Warranty has expired since ".$warrantyExpiration->format('Y-m-d');
                    //return new Response("RMA recorded. Warranty has expired since ".$warrantyExpiration->format('Y-m-d'));
                    else
                        $message = "Unit is still under warranty until ".$warrantyExpiration->format('Y-m-d');
                        //return new Response("RMA recorded. Unit is still under warranty until ".$warrantyExpiration->format('Y-m-d'));
                    
                }
                return $this->render('AchPoManagerBundle:PoManager:successGeneric.html.twig', array('returnPath' => 'ach_po_manager_process_rma_create', 'message1' => 'RMA # '.$rmaInstance->getNum().' on unit '.$rmaInstance->getSerialNum()->getSerialNumber().' has been created successfully!', 'message2' => $message, 'pageTitle' => 'RMA created!'));
                //return $this->render('AchPoManagerBundle:PoManager:successRmaCreated.html.twig', array('rmaInstance' => $rmaInstance, 'returnPath' => 'ach_po_manager_process_rma_create', 'message' => $message ));
            }
        }

        return $this->render('AchPoManagerBundle:PoManager:createRma.html.twig', array('form' => $formRma->createView()));

    }


    public function receiveRmaAction($location)
    {
        $repoRepairLocation = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairLocation');
        $repairLocationInstance = $repoRepairLocation->findOneByName($location);
        if(empty($repairLocationInstance)) {
            return new Response("Location of repair could not be determined");
        }

        $rmaReceive = new Rma();
        $formReceiveRma = $this->createForm(new RmaReceiveType, $rmaReceive);

        $request = $this->get('request');
        
        if($request->getMethod() == 'POST') {
            $formReceiveRma->bind($request);
            if ($formReceiveRma->isValid()) {

                $repoRma = $this->getDoctrine()->getRepository('AchPoManagerBundle:Rma');
                $rmaInstance = $repoRma->findWaitingReceptionBySn($rmaReceive->getSerialNumF(), $repairLocationInstance->getId());
                if(is_null($rmaInstance)) {
                    $message = "There is currently no RMA waiting under this S/N";
                    return $this->render('AchPoManagerBundle:PoManager:error.html.twig', array('message' => $message, 'returnPath' => 'ach_po_manager_process_rma_receive', 'repairLocation' => $repairLocationInstance->getName() ));
                }
                else {
                    // update the repair Status
                    $repoRepairStatus = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairStatus');
                    $repairStatus = $repoRepairStatus->findOneByName('Received');
                    $rmaInstance->setRepairStatus($repairStatus);

                    // record date of reception
                    $rmaInstance->setReceptionDate(new \Datetime());

                    // cat comment
                    $rmaInstance->setComment($rmaInstance->getComment() . 'Comment @reception: ' . $rmaReceive->getComment());

                    $em = $this->getDoctrine()->getManager();
                    $em->flush();

                    return $this->render('AchPoManagerBundle:PoManager:successGeneric.html.twig', array('rmaInstance' => $rmaInstance, 'returnPath' => 'ach_po_manager_process_rma_receive', 'repairLocation'=> $location, 'message1' => "RMA received successfully", 'pageTitle' => 'RMA received'));
                    
                }
                
            }
        }

        return $this->render('AchPoManagerBundle:PoManager:receiveRma.html.twig', array('form' => $formReceiveRma->createView()));
               
    }


    public function repairRmaAction($location)
    {
        $repoRepairLocation = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairLocation');
        $repairLocationInstance = $repoRepairLocation->findOneByName($location);
        if(empty($repairLocationInstance)) {
            return new Response("Location of repair could not be determined");
        }

        $rmaRetrieve = new Rma();
        $formRetrieveRma = $this->createForm(new RmaRetrieveType, $rmaRetrieve);

        $request = $this->get('request');
        
        if($request->getMethod() == 'POST') {
            $formRetrieveRma->bind($request);
            if ($formRetrieveRma->isValid()) {
                
                return $this->redirect($this->generateUrl('ach_po_manager_process_rma_update', array('location' => $location, 'sn' => $rmaRetrieve->getSerialNumF() ) ) );
            }
        }

        return $this->render('AchPoManagerBundle:PoManager:retrieveRma.html.twig', array('form' => $formRetrieveRma->createView()));
               
    }

    public function updateRmaAction($location, $sn)
    {
        $repoRepairLocation = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairLocation');
        $repairLocationInstance = $repoRepairLocation->findOneByName($location);
        if(empty($repairLocationInstance)) {
            return new Response("Location of repair could not be determined");
        }

        $repoRma = $this->getDoctrine()->getRepository('AchPoManagerBundle:Rma');
        $rmaInstance = $repoRma->findReceivedBySn($sn, $location);
        if(empty($rmaInstance)) {
            return new Response("Could not fine any RMA to be repaired with this S/N at this location");
        }

        $rmaInstance->setSerialNumF($sn);

        //get product that is concerned by RMA
        $productInstance=$rmaInstance->getSerialNum()->getShipmentBatch()->getShipmentItem()->getPoItem()->getRevision()->getProduct();

        $formUpdateRma = $this->createForm(new RmaUpdateType($productInstance), $rmaInstance);

        $request = $this->get('request');
        
        if($request->getMethod() == 'POST') {
            $formUpdateRma->bind($request);
             if ($formUpdateRma->isValid()) {
                 $em = $this->getDoctrine()->getManager();
                 foreach($rmaInstance->getPartReplacements() as $partReplacement)
                     {
                         $partReplacement->setRma($rmaInstance);
                     }
                 // record repairDate
                 $rmaInstance->setRepairDate(new \Datetime());
                 $em->flush();
                 return $this->render('AchPoManagerBundle:PoManager:successRmaUpdated.html.twig', array('rmaInstance' => $rmaInstance, 'returnPath' => 'ach_po_manager_process_rma_repair', 'repairLocation'=> $location, 'sn'=> $sn));
                 //return $this->redirect($this->generateUrl('ach_po_manager_process_rma_update', array('location' => $location, 'sn' => $rmaRetrieve->getSerialNumF() ) ) );
            }
        }

        return $this->render('AchPoManagerBundle:PoManager:updateRma.html.twig', array('form' => $formUpdateRma->createView(), 'rmaInstance' => $rmaInstance));
        
        //return new Response('location: '.$location.' - sn: '.$sn);

    }

    public function endRepairRmaAction($location, $sn)
    {
        $repoRepairLocation = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairLocation');
        $repairLocationInstance = $repoRepairLocation->findOneByName($location);
        if(empty($repairLocationInstance)) {
            return new Response("Location of repair could not be determined");
        }
        
        $repoRma = $this->getDoctrine()->getRepository('AchPoManagerBundle:Rma');
        $rmaInstance = $repoRma->findRepairedBySn($sn, $location);
        if(empty($rmaInstance)) {
            return new Response("Could not fine any RMA already investigated with this S/N at this location");
        }

        $repoRepairStatus = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairStatus');
        $repairStatusInstance = $repoRepairStatus->findOneByName('Returned_to_Stock');
        $rmaInstance->setRepairStatus($repairStatusInstance);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new Response("RMA unit is now ready for final inspection");
    }

    public function notRepairRmaAction($location, $sn)
    {
        $repoRepairLocation = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairLocation');
        $repairLocationInstance = $repoRepairLocation->findOneByName($location);
        if(empty($repairLocationInstance)) {
            return new Response("Location of repair could not be determined");
        }
        
        $repoRma = $this->getDoctrine()->getRepository('AchPoManagerBundle:Rma');
        $rmaInstance = $repoRma->findRepairedBySn($sn, $location);
        if(empty($rmaInstance)) {
            return new Response("Could not fine any RMA already investigated with this S/N at this location");
        }

        $repoRepairStatus = $this->getDoctrine()->getRepository('AchPoManagerBundle:RepairStatus');
        $repairStatusInstance = $repoRepairStatus->findOneByName('Not_Repairable');
        $rmaInstance->setRepairStatus($repairStatusInstance);

        $em = $this->getDoctrine()->getManager();
        $em->flush();
        
        return new Response("RMA unit now been decommissioned. Please set unit aside and mark it as non prod.");
    }

    public function addProblemCategory($pn)
    {
        

    }
    
    /* public function doesSerialNumberExistAction($sn) */
    /* { */
    /*     $repositorySerialNumber = $this->getDoctrine()->getRepository('AchPoManagerBundle:SerialNumber'); */

    /*     return new Response(); */
    /* } */


}