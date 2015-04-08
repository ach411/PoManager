<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RevisionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RevisionRepository extends EntityRepository
{
	public function findRevProduct($custRev, $custPn)
	{
		$query = $this->_em->createQuery('SELECT r, p FROM AchPoManagerBundle:Revision r JOIN r.product p WHERE r.revisionCust = :custRev AND p.custPn = :custPn');
		$query->setParameter('custRev', $custRev);
		$query->setParameter('custPn', $custPn);
		//return $query->getResult();
		return $query->getOneOrNullResult();
	}

	public function findLatestActiveRev($pn)
	{
		$query = $this->_em->createQuery('SELECT r, p FROM AchPoManagerBundle:Revision r JOIN r.product p WHERE p.pn = :pn AND r.revisionCust NOT LIKE \'unknown\' AND r.active = 1 ORDER BY r.revisionCust DESC')
					->setMaxResults(1);
		$query->setParameter('pn', $pn);
		return $query->getOneOrNullResult();
		//return $query->getResult();
	}
	
	public function findByPnUnknownRevision($pn)
	{
		$query = $this->_em->createQuery('SELECT r, p FROM AchPoManagerBundle:Revision r JOIN r.product p WHERE p.pn = :pn AND r.revisionCust LIKE \'unknown\'')
				->setMaxResults(1);
		$query->setParameter('pn', $pn);
		return $query->getOneOrNullResult();
	}
	
	public function findByCustPn($custPn)
	{
		$query = $this->_em->createQuery('SELECT r, p FROM AchPoManagerBundle:Revision r JOIN r.product p WHERE p.custPn LIKE :custPn');
		
		$query->setParameter('custPn', '%' . $custPn . '%');
		
		return $query->getResult();
	}
	
	public function findByDescription($desc)
	{
		$query = $this->_em->createQuery('SELECT r, p FROM AchPoManagerBundle:Revision r JOIN r.product p WHERE p.description like :desc');
		
		$query->setParameter('desc', '%' . $desc . '%');
		
		return $query->getResult();
	}
}
