<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportLeague;

class SportLeagueRepository extends EntityRepository
{

    /**
     * Get sport league object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|SportLeague
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
     * Get sport leagues by article category ID value.
     * 
     * @access public
     * @param int $articleCategoryLvl2Id
     * @return array
     */
    public function findAllByArticleCategoryId($articleCategoryLvl2Id = null)
    {
        if ($articleCategoryLvl2Id === null) {
            return [];
        }

        $qb = $this->createQueryBuilder('l')
                ->select('l')
                ->where('l.articleCategoryLvl2 = :categoryId')
                ->setParameter('categoryId', $articleCategoryLvl2Id);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sport leagues list.
     * 
     * @access public
     * @param null|int $sportTypeId
     * @return array
     */
    public function getList($sportTypeId = null)
    {
        $qb = $this->createQueryBuilder('l')->select('l.id, l.title');
        if ($sportTypeId !== null && (int)$sportTypeId > 0) {
            $qb->where('l.sportType = :typeId')->setParameter('typeId', $sportTypeId);
        }
        $qb->orderBy('l.title', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}