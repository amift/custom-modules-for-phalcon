<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportStatsOverall;

class SportStatsOverallRepository extends EntityRepository
{

    /**
     * Get SportStatsOverall object by main params values.
     * 
     * @access public
     * @param int $sportTypeId
     * @param int $sportLeagueId
     * @param int $sportLeagueGroupId
     * @param int $sportSeasonId
     * @param int $sportTeamId
     * @return null|SportStatsOverall
     */
    public function findObjectByMainParams($sportTypeId = null, $sportLeagueId = null, $sportLeagueGroupId = null, $sportSeasonId = null, $sportTeamId = null)
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
        if ($sportLeagueGroupId !== null) {
                $qb->andWhere('t.sportLeagueGroup = :sportLeagueGroupId')
                ->setParameter('sportLeagueGroupId', $sportLeagueGroupId);
        }
        if ($sportSeasonId !== null) {
                $qb->andWhere('t.sportSeason = :sportSeasonId')
                ->setParameter('sportSeasonId', $sportSeasonId);
        }
        if ($sportTeamId !== null) {
                $qb->andWhere('t.sportTeam = :sportTeamId')
                ->setParameter('sportTeamId', $sportTeamId);
        }
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get SportStatsOverall results with status data by main params values.
     * 
     * @access public
     * @param int $sportTypeId
     * @param int $sportLeagueId
     * @param int $sportLeagueGroupId
     * @param int $sportSeasonId
     * @return Query|null
     */
    public function getOverAllStatsTable($sportTypeId = null, $sportLeagueId = null, $sportLeagueGroupId = null, $sportSeasonId = null)
    {
        if ($sportTypeId === null || $sportLeagueId === null || $sportLeagueGroupId === null || $sportSeasonId === null) {
            return null;
        }

        $qb = $this->createQueryBuilder('t')
                ->select('t')
                ->andWhere('t.sportType = :sportTypeId')
                ->setParameter('sportTypeId', $sportTypeId)
                ->andWhere('t.sportLeague = :sportLeagueId')
                ->setParameter('sportLeagueId', $sportLeagueId)
                ->andWhere('t.sportLeagueGroup = :sportLeagueGroupId')
                ->setParameter('sportLeagueGroupId', $sportLeagueGroupId)
                ->andWhere('t.sportSeason = :sportSeasonId')
                ->setParameter('sportSeasonId', $sportSeasonId)
                ->orderBy('t.ordering', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getStatsActualLeagueGroupsIds($sportType, $sportLeague, $season)
    {
        $sportLeagueGroupIds = [];

        $qb = $this->createQueryBuilder('so')
                ->select('leagueGroup.id AS leagueGroupId')
                ->leftJoin('so.sportLeagueGroup', 'leagueGroup')
                ->andWhere('so.sportType = :sportTypeId')
                ->setParameter('sportTypeId', $sportType->getId())
                ->andWhere('so.sportLeague = :sportLeagueId')
                ->setParameter('sportLeagueId', $sportLeague->getId())
                ->andWhere('so.sportSeason = :sportSeasonId')
                ->setParameter('sportSeasonId', $season->getId())
                ->groupBy('so.sportLeagueGroup');
                //->orderBy('leagueGroup.title', 'ASC');

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $sportLeagueGroupIds[] = $row['leagueGroupId'];
        }

        return $sportLeagueGroupIds;
    }

}