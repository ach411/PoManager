<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ShipmentBatchRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShipmentBatchRepository extends EntityRepository
{
    public function findAvailableByProductName($productName)
    {
        $query = $this->_em->createQuery('SELECT l FROM AchPoManagerBundle:ShipmentBatch l WHERE l.productName like :productName AND l.shipmentItem is NULL');
        $query->setParameter('productName', $productName);
        return $query->getResult();
    }

    public function findWaitingForRemovalByProductName($productName)
    {
        $query = $this->_em->createQuery('SELECT l FROM AchPoManagerBundle:ShipmentBatch l WHERE l.productName like :productName AND l.shipmentItem is NULL AND l.waitingForRemoval = true');
        $query->setParameter('productName', $productName);
        return $query->getResult();
    }

    public function findByProductNameAndLotNumber($productName, $lotNum)
    {
        $query = $this->_em->createQuery('SELECT l FROM AchPoManagerBundle:ShipmentBatch l WHERE l.productName like :productName AND l.num like :num');
        $query->setParameter('productName', $productName);
        $query->setParameter('num', $lotNum);
        return $query->getResult();
    }
        
}