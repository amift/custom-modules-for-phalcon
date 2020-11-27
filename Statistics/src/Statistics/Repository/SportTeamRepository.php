<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportTeam;

class SportTeamRepository extends EntityRepository
{

    /**
     * Get sport team object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|SportTeam
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('t')
                ->select('t')
                ->where('t.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get sport team object by sport type, sport league and sport team key values.
     * 
     * @access public
     * @param int $sportTypeId
     * @param int $sportLeagueId
     * @param string $key
     * @return null|SportTeam
     */
    public function findObjectByMainParams($sportTypeId = null, $sportLeagueId = null, $key = '')
    {
        $qb = $this->createQueryBuilder('t')
                ->select('t');
        if ($sportTypeId !== null) {
                $qb->andWhere('t.sportType = :sportTypeId')
                ->setParameter('sportTypeId', $sportTypeId);
        }
        if ($sportLeagueId !== null) {
                $qb->andWhere('t.sportLeague = :sportLeagueId')
                ->setParameter('sportLeagueId', $sportLeagueId);
        }
        if ($key !== '') {
                $qb->andWhere('t.key = :key')
                ->setParameter('key', $key);
        }
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get sport teams list.
     * 
     * @access public
     * @param null|int $sportTypeId
     * @param null|int $sportLeagueId
     * @return array
     */
    public function getList($sportTypeId = null, $sportLeagueId = null)
    {
        $qb = $this->createQueryBuilder('l')->select('l.id, l.title');
        if ($sportTypeId !== null && (int)$sportTypeId > 0) {
            $qb->where('l.sportType = :typeId')->setParameter('typeId', $sportTypeId);
        }
        if ($sportLeagueId !== null && (int)$sportLeagueId > 0) {
            $qb->where('l.sportLeague = :leagueId')->setParameter('leagueId', $sportLeagueId);
        }
        $qb->orderBy('l.title', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}