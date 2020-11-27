<?php

namespace System\Repository;

use Doctrine\ORM\EntityRepository;
use System\Entity\CronJob;

class CronJobRepository extends EntityRepository
{

    /**
     * Get CronJob object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|CronJob
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObjectByCronAction($cronAction = '')
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.cronAction = :cronAction')
                ->setParameter('cronAction', $cronAction)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
