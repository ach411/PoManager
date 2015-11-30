<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ach\PoManagerBundle\Entity\PoItem;
use Ach\PoManagerBundle\Entity\Invoice;

class PoManagerSearchSerialNumberController extends Controller
{
    
    public function searchSerialNumberAction($sn)
    {
        $repoSerialNumber = $this->getDoctrine()
								->getManager()
								->getRepository('AchPoManagerBundle:SerialNumber');
        
        $request = $this->getRequest();

        if($request->query->get('match') == 'exact') {
            $exact = true;
        }
        else {
            $exact = false;
        }

        $serialNumberInstances = $repoSerialNumber->findBySerialNumber($sn, $exact);
		
		return $this->generateResponse($request, $serialNumberInstances);
    }

    public function searchSerialNumberMacAction($mac)
    {
        $repoSerialNumber = $this->getDoctrine()
								->getManager()
								->getRepository('AchPoManagerBundle:SerialNumber');
        
        $request = $this->getRequest();
		
		$serialNumberInstances = $repoSerialNumber->findByMacAddress($mac);
		
		return $this->generateResponse($request, $serialNumberInstances);
    }


	/* Generate response depending on the option */
	private function generateResponse($request, $serialNumberInstances)
	{
		if($request->query->get('return') == 'xls')
		{
			//return $this->generateShipmentItemXls($shipmentItems);
		}
		elseif($request->query->get('return') == 'json')
		{
			return $this->generateResponseJson($serialNumberInstances);
		}
		else
		{
			return $this->render('AchPoManagerBundle:PoManager:displayListSerialNumber.html.twig', array('serialNumbers' => $serialNumberInstances));
		}
	}

    private function generateResponseJson($serialNumberInstances)
    {
        $jsonTable = array();
        foreach($serialNumberInstances as $sn) {
            $jsonTable[] = array("SN" => $sn->getSerialNumber(), "MAC" => $sn->getMacAddress());
        }
        $response = new JsonResponse();
        $response->setData($jsonTable);
        return $response;
    }


}