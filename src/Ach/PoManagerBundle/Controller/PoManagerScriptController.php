<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class PoManagerScriptController extends Controller
{

    public function jsonReturnLatestActiveRevAction($pn)
    {
	$repository = $this->getDoctrine()
	                   ->getManager()
			   ->getRepository('AchPoManagerBundle:Revision');
	$activeRev = $repository->findLatestActiveRev($pn);
	// select only first line
	$table = array("PN" => $activeRev->getProduct()->getPn(), "SKPN" => $activeRev->getProduct()->getCustPn() , "REV" => $activeRev->getRevisionCust());
	$response = new JsonResponse();
	$response->setData($table);
	return $response;
    }	


}