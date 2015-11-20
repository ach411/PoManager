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
		
		$serialNumberInstances = $repoSerialNumber->findBySerialNumber($sn);
		
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
			//return $this->generatePoItemJson($shipmentItems);
		}
		else
		{
			return $this->render('AchPoManagerBundle:PoManager:displayListSerialNumber.html.twig', array('serialNumbers' => $serialNumberInstances));
		}
	}


}