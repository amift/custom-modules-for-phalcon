<?php

namespace Articles\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Articles\Entity\Article;
use Articles\Tool\State;
use Articles\Tool\Type;

class ArticleRepository extends EntityRepository
{

    /**
     * Get article object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Article
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('a')
                ->select('a')
                ->where('a.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getOutputArticlesQueryBuilder($categoryFieldName = null, $categoryFieldValue = null)
    {
        $qb = $this->createQueryBuilder('a')
                ->select('a, c1')
                ->leftJoin('a.categoryLvl1', 'c1')
                ->where('a.state IN (:states)')
                ->setParameter('states', State::getOutputStates());

        if ($categoryFieldName !== null && $categoryFieldValue !== null) {
            $qb->andWhere('a.' . $categoryFieldName . ' IN (:category)')
                ->setParameter('category', $categoryFieldValue);
        } else {
            $qb->andWhere('a.startpage = :startpage')
                ->setParameter('startpage', true);
        }
        $qb->orderBy('a.id', 'DESC');

        return $qb;
    }

    public function getPromoArticle($categoryFieldName = null, $categoryFieldValue = null)
    {
        $qb = $this->getOutputArticlesQueryBuilder($categoryFieldName, $categoryFieldValue);
        $qb->andWhere('a.promo = :promo')->setParameter('promo', true);
        $qb->setMaxResults(1);

        //return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getActualVideoArticles($categoryFieldName = null, $categoryFieldValue = null, $excludeIds = [], $page = 1, $limit = 3)
    {
        $qb = $this->getOutputArticlesQueryBuilder($categoryFieldName, $categoryFieldValue);
        $qb->andWhere('a.actual = :actual')->setParameter('actual', true);
        $qb->andWhere('a.type = :type')->setParameter('type', Type::TYPE_VIDEO);
        if (count($excludeIds) > 0) {
            $qb->andWhere('a.id NOT IN (:excludeIds)')->setParameter('excludeIds', $excludeIds);
        }
        $qb->setMaxResults($limit);

        //return $qb->getQuery()->getArrayResult();
        return $qb->getQuery()->getResult();
    }

    public function getActualNewsArticles($categoryFieldName = null, $categoryFieldValue = null, $excludeIds = [], $page = 1, $limit = 9)
    {
        $qb = $this->getOutputArticlesQueryBuilder($categoryFieldName, $categoryFieldValue);
        $qb->andWhere('a.actual = :actual')->setParameter('actual', true);
        //$qb->andWhere('a.type = :type')->setParameter('type', Type::TYPE_NEWS);
        if (count($excludeIds) > 0) {
            $qb->andWhere('a.id NOT IN (:excludeIds)')->setParameter('excludeIds', $excludeIds);
        }
        $qb->setMaxResults($limit);

        //return $qb->getQuery()->getArrayResult();
        return $qb->getQuery()->getResult();
    }

    public function getCenterArticles($categoryFieldName = null, $categoryFieldValue = null, $excludeIds = [], $page = 1, $perPage = 10)
    {
        $qb = $this->getOutputArticlesQueryBuilder($categoryFieldName, $categoryFieldValue);
        if (count($excludeIds) > 0) {
            $qb->andWhere('a.id NOT IN (:excludeIds)')->setParameter('excludeIds', $excludeIds);
        }

        $qb->setMaxResults($perPage);

        //if ($categoryFieldName !== null && $categoryFieldValue !== null) {
            $qb->setFirstResult(($page - 1) * $perPage);

            return new Paginator($qb->getQuery(), true);
        //}

        //return $qb->getQuery()->getResult();
    }

    public function getMemberArticlesQuery($memberId, $page = 1, $perPage = 10)
    {
        $qb = $this->createQueryBuilder('a')
                ->select('a, c1')
                ->leftJoin('a.categoryLvl1', 'c1')
                ->where('a.member = :memberId')
                ->setParameter('memberId', $memberId)
                ->orderBy('a.id', 'DESC')
                ->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

        return $qb->getQuery();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('a')
                ->select('COUNT(a.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

}
