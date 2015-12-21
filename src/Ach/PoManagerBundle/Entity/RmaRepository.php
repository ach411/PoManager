<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RmaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RmaRepository extends EntityRepository
{
    public function findOpenedBySn($sn)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t WHERE s.serialNumber like :sn and t.name not like \'Returned_to_Customer\'');
        $query->setParameter('sn', $sn);
        return $query->getOneOrNullResult();
    }

    public function findWaitingReceptionBySn($sn, $repairLocationId)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t JOIN r.repairLocation l WHERE s.serialNumber like :sn and t.name like \'Waiting_for_Reception\' and l.id = :locationid ');
        $query->setParameter('sn', $sn);
        $query->setParameter('locationid', $repairLocationId);
        return $query->getOneOrNullResult();
    }


    public function findReceivedBySn($sn, $repairLocation)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t JOIN r.repairLocation l WHERE s.serialNumber like :sn and (t.name like \'Received\' or t.name like \'Returned_to_Stock\') and l.name like :location ');
        $query->setParameter('sn', $sn);
        $query->setParameter('location', $repairLocation);
        return $query->getOneOrNullResult();
    }

    public function findRepairedBySn($sn, $repairLocation)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t JOIN r.repairLocation l WHERE s.serialNumber like :sn and (t.name like \'Received\' or t.name like \'Returned_to_Stock\') and l.name like :location and r.repairDate is not null');
        $query->setParameter('sn', $sn);
        $query->setParameter('location', $repairLocation);
        return $query->getOneOrNullResult();
    }

    public function findBackToStockBySn($sn, $repairLocation)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t JOIN r.repairLocation l WHERE s.serialNumber like :sn and t.name like \'Returned_to_Stock\' and l.name like :location and r.repairDate is not null');
        $query->setParameter('sn', $sn);
        $query->setParameter('location', $repairLocation);
        return $query->getOneOrNullResult();
    }

    public function findReadyToShipBySn($sn, $repairLocation)
    {
        $query = $this->_em->createQuery('SELECT r FROM AchPoManagerBundle:Rma r JOIN r.serialNum s JOIN r.repairStatus t JOIN r.repairLocation l WHERE s.serialNumber like :sn and t.name like \'Ready_to_Ship\' and l.name like :location and r.repairDate is not null');
        $query->setParameter('sn', $sn);
        $query->setParameter('location', $repairLocation);
        return $query->getOneOrNullResult();
    }

}
