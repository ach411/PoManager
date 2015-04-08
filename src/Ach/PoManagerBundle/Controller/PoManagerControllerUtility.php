<?php

namespace Ach\PoManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Ach\PoManagerBundle\Entity\Status;
use Ach\PoManagerBundle\Entity\Notification;

/* This Class contains Utility codes for all the controllers */
class PoManagerControllerUtility extends Controller
{

	/* Convert date minMax into array containing earliest and latest date in standard format */
	public static function convertDateFilter($minDate, $maxDate)
	{
		if($minDate != "-1y")
		{
			$earliestDate = new \DateTime($minDate);
		}
		else
		{
			$date = new \DateTime('now');
			$earliestDate = $date->sub(new \DateInterval('P1Y'));
			// $earliestDate = strtotime("-1 year");
		}
		//echo $earliestDate->format('Y-m-d');
	
		if($maxDate != "+1y")
		{
			if($maxDate == "+0d")
				$latestDate = $earliestDate;
			else
			//$latestDate = strtotime($maxDate);
				$latestDate = new \DateTime($maxDate);
		}
		else
		{
			$date = new \DateTime('now');
			$latestDate = $date->add(new \DateInterval('P1Y'));
			// $latestDate = strtotime("+1 year");
		}
		//echo $latestDate->format('Y-m-d');
	
		$filterDate['earliest'] = $earliestDate;
		$filterDate['latest'] = $latestDate;
	
		return $filterDate;
	}
	
}