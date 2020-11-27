<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportLeagueGroup;

class SportLeagueGroupRepository extends EntityRepository
{

    /**
     * Get sport league group object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|SportLeagueGroup
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('l')
                ->select('l')
                ->where('l.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get sport league groups list.
     * 
     * @access public
     * @param null|int $sportLeagueId
     * @return array
     */
    public function getList($sportLeagueId = null)
    {
        $qb = $this->createQueryBuilder('l')->select('l.id, l.title');
        if ($sportLeagueId !== null && (int)$sportLeagueId > 0) {
            $qb->where('l.sportLeague = :leagueId')->setParameter('leagueId', $sportLeagueId);
        }
        $qb->orderBy('l.title', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}