<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportType;

class SportTypeRepository extends EntityRepository
{

    /**
     * Get sport type object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|SportType
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
     * Get sport types by article category ID value.
     * 
     * @access public
     * @param int $articleCategoryLvl1Id
     * @return array
     */
    public function findAllByArticleCategoryId($articleCategoryLvl1Id = null)
    {
        if ($articleCategoryLvl1Id === null) {
            return [];
        }

        $qb = $this->createQueryBuilder('t')
                ->select('t')
                ->where('t.articleCategoryLvl1 = :categoryId')
                ->setParameter('categoryId', $articleCategoryLvl1Id);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sport types list.
     * 
     * @access public
     * @return array
     */
    public function getList()
    {
        $qb = $this->createQueryBuilder('t')
                ->select('t.id, t.title')
                ->orderBy('t.title', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}