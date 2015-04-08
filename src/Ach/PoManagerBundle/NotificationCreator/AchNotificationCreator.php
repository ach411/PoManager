<?php

namespace Ach\PoManagerBundle\NotificationCreator;

use Doctrine\ORM\EntityManager;
use Ach\PoManagerBundle\Entity\Notification;

class AchNotificationCreator
{
	protected $em;
	
	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
	}
	
	public function createNotification($notificationSource, $notificationCategory)
	{
		$repositoryNotificationCategory = $this->em->getRepository('AchPoManagerBundle:NotificationCategory');
		return new Notification($notificationSource, $repositoryNotificationCategory->findOneByName($notificationCategory));
	}

}