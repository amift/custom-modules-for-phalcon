<?php

namespace Members\Repository;

use Doctrine\ORM\EntityRepository;
use Members\Entity\Withdraws;
use Members\Tool\WithdrawState;

class WithdrawsRepository extends EntityRepository
{

    /**
     * Get withdraw object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Withdraws
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('w')
                ->select('w')
                ->where('w.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('w')
                ->select('COUNT(w.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getMemberWithdrawsQuery($memberId, $page = 1, $perPage = 10)
    {
        $qb = $this->createQueryBuilder('w')
                ->select('w')
                ->where('w.member = :memberId')
                ->setParameter('memberId', $memberId)
                ->orderBy('w.id', 'DESC')
                ->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

        return $qb->getQuery();
    }

    public function memberHasPendingRequest($memberId)
    {
        $qb = $this->createQueryBuilder('w')
                ->select('COUNT(w.id) AS cnt')
                ->where('w.member = :memberId')
                ->setParameter('memberId', $memberId)
                ->andWhere('w.state IN (:states)')
                ->setParameter('states', WithdrawState::getPendingStates());

        return $qb->getQuery()->getSingleScalarResult();
    }

}
