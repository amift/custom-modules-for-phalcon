<?php

namespace Polls\Repository;

use Doctrine\ORM\EntityRepository;
use Polls\Entity\Poll;
use Polls\Tool\State;

class PollRepository extends EntityRepository
{

    /**
     * Get poll object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Poll
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObjectByCategory1Level($categoryId = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.categoryLvl1 = :categoryId')
                ->setParameter('categoryId', $categoryId)
                ->andWhere('p.state = :state')
                ->setParameter('state', State::STATE_ACTIVE)
                ->setMaxResults(1)
                ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObjectByCategory2Level($categoryId = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.categoryLvl2 = :categoryId')
                ->setParameter('categoryId', $categoryId)
                ->andWhere('p.state = :state')
                ->setParameter('state', State::STATE_ACTIVE)
                ->setMaxResults(1)
                ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObjectByCategory3Level($categoryId = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.categoryLvl3 = :categoryId')
                ->setParameter('categoryId', $categoryId)
                ->andWhere('p.state = :state')
                ->setParameter('state', State::STATE_ACTIVE)
                ->setMaxResults(1)
                ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObjectStartpage()
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.startpage = :startpage')
                ->setParameter('startpage', true)
                ->andWhere('p.state = :state')
                ->setParameter('state', State::STATE_ACTIVE)
                ->setMaxResults(1)
                ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('p')
                ->select('COUNT(p.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

}
