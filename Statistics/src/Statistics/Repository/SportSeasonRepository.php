<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportSeason;

class SportSeasonRepository extends EntityRepository
{

    /**
     * Get sport season object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|SportSeason
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('s')
                ->select('s')
                ->where('s.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Update season as actual
     * 
     * @access public
     * @param SportSeason $season
     * @return void
     */
    public function setActual(SportSeason $season)
    {
        $qb = $this->createQueryBuilder('s')
                ->update()
                ->set('s.actual', ':false')->setParameter('false', false)
                ->where('s.actual = :true')->setParameter('true', true)
                ->andWhere('s.id != :id')->setParameter('id', $season->getId());

        $sportType = $season->getSportType();
        if (is_object($sportType)) {
            $qb->andWhere('s.sportType = :sportType')->setParameter('sportType', $sportType->getId());
        }

        $sportLeague = $season->getSportLeague();
        if (is_object($sportLeague)) {
            $qb->andWhere('s.sportLeague = :sportLeague')->setParameter('sportLeague', $sportLeague->getId());
        }

        $qb->getQuery()->execute();
    }

    /**
     * 
     * @return \Doctrine\ORM\Query
     */
    public function getAllActualQuery($sportTypeIds = [], $sportLeagueIds = [])
    {
        $qb = $this->createQueryBuilder('s')
                ->select('s')
                ->where('s.actual = :true')
                ->setParameter('true', true);

        if (count($sportTypeIds) > 0) {
            $qb->andWhere('s.sportType IN (:typeIds)')->setParameter('typeIds', $sportTypeIds);
        }

        if (count($sportLeagueIds) > 0) {
            $qb->andWhere('s.sportLeague IN (:leagueIds)')->setParameter('leagueIds', $sportLeagueIds);
        }

        $qb->orderBy('s.priority', 'DESC');

        return $qb->getQuery();
    }

    /**
     * Get sport seasons list.
     * 
     * @access public
     * @param null|int $sportTypeId
     * @param null|int $sportLeagueId
     * @return array
     */
    public function getList($sportTypeId = null, $sportLeagueId = null)
    {
        $qb = $this->createQueryBuilder('s')->select('s.id, s.title');
        if ($sportTypeId !== null && (int)$sportTypeId > 0) {
            $qb->andWhere('s.sportType = :typeId')->setParameter('typeId', $sportTypeId);
        }
        if ($sportLeagueId !== null && (int)$sportLeagueId > 0) {
            $qb->andWhere('s.sportLeague = :leagueId')->setParameter('leagueId', $sportLeagueId);
        }
        $qb->orderBy('s.title', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}