<?php

namespace Forum\Repository;

use Doctrine\ORM\EntityRepository;
use Forum\Entity\ForumCategory;

class ForumCategoryRepository extends EntityRepository
{

    /**
     * Get forum category object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|ForumCategory
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

    /**
     * Get valid next ordering value for new forum category.
     * 
     * @access public
     * @return int
     */
    public function getNextOrderingValue()
    {
        $qb = $this->createQueryBuilder('c')
                ->select('MAX(c.ordering)')
                ->setMaxResults(1);

        $ordering = $qb->getQuery()->getSingleScalarResult();
        if ($ordering === null) {
            return 1;
        }

        return $ordering + 1;
    }

    /**
     * Get next/prev object for ordering change
     * 
     * @access public
     * @param int $ordering
     * @param null|ForumCategory $parent
     * @param string $direction
     * @return type
     */
    public function getNextObjectForOrderingChange($ordering, $parent = null, $direction = 'down')
    {
        $qb = $this->createQueryBuilder('c')->select('c');
        if ($parent === null) {
            $qb->where('c.parent IS NULL');
        } else {
            $qb->where('c.parent = :parentId')->setParameter('parentId', $parent->getId());
        }
        $qb->andWhere('c.ordering ' . ($direction == 'up' ? '<' : '>') . ' :ordering')
                ->setParameter('ordering', $ordering)
                ->orderBy('c.ordering', ($direction == 'up' ? 'DESC' : 'ASC'))
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getEnabledCategoriesSlugList()
    {
        $qb = $this->createQueryBuilder('c')
                ->select("c.id, c.title, c.slug, COALESCE(p1.slug, '') AS parent1Slug, COALESCE(p2.slug, '') AS parent2Slug, c.level")
                ->leftJoin('c.parent', 'p1')
                ->leftJoin('p1.parent', 'p2')
                ->where('c.enabled = :enabled')
                ->setParameter('enabled', true)
                ->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getCategoriesList($parentSlug = null, $maxLevel = 2)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, p.id AS parent, c.title, c.slug, c.ordering, c.content, c.topicsCount, c.commentsCount, c.viewsCount')
                ->leftJoin('c.parent', 'p')
                ->where('c.enabled = :enabled')
                ->setParameter('enabled', true)
                ->andWhere('c.level <= :level')
                ->setParameter('level', $maxLevel);

        if ($parentSlug !== null) {
            $qb->andWhere('(c.slug = :slug OR p.slug = :slug)')
                ->setParameter('slug', $parentSlug);
        }

        $qb->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getParentsList($maxLevel = 2)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, p.id AS parent, c.title, c.slug, c.ordering')
                ->leftJoin('c.parent', 'p')
                ->where('c.level <= :level')
                ->setParameter('level', $maxLevel)
                ->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getParentsListFromLevel($level = 1, $parentId = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, p.id AS parent, c.title, c.slug, c.ordering, c.content, c.topicsCount, c.commentsCount, c.viewsCount')
                ->leftJoin('c.parent', 'p')
                ->where('c.level = :level')
                ->setParameter('level', $level);
        if ($parentId !== null) {
            $qb->andWhere('c.parent = :parent')
                ->setParameter('parent', $parentId);
        }
        $qb->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getParentChildsList($parentId = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, p.id AS parent, c.title, c.slug, c.ordering')
                ->leftJoin('c.parent', 'p');
        if ($parentId !== null) {
            $qb->andWhere('c.parent = :parent')
                ->setParameter('parent', $parentId);
        } else {
            $qb->andWhere('c.parent IS NULL');
        }
        $qb->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getParentsListObjectSelect($maxLevel = 2)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.level <= :level')
                ->setParameter('level', $maxLevel)
                ->orderBy('c.ordering', 'ASC')
                ->addOrderBy('c.parent', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findObjectBySlugAndParentFromLevel($slug = '', $parent = 0, $level = 1)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.slug = :slug')
                ->setParameter('slug', $slug);
        if ((int)$level === 2) {
            $qb
                ->andWhere('c.parent = :parent')
                ->setParameter('parent', $parent)
                ->andWhere('c.level = :level')
                ->setParameter('level', 2);
        } elseif ((int)$level === 3) {
            $qb
                ->andWhere('c.parent = :parent')
                ->setParameter('parent', $parent)
                ->andWhere('c.level = :level')
                ->setParameter('level', 3);
        } else {
            $qb
                ->andWhere('c.parent IS NULL')
                ->andWhere('c.level = :level')
                ->setParameter('level', 1);
        }
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('c')
                ->select('COUNT(c.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getCategoriesListBySlug($slug = null, $parentSlug = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, c.title, c.slug, c.content, c.topicsCount, c.commentsCount, c.viewsCount, t.id AS topicId, t.slug AS topicSlug, t.title AS topicTitle, c.lastReplyByUsername, c.lastReplyAt, c.lastReply')
                ->leftJoin('c.lastReplyOnTopic', 't');

        if ($parentSlug !== null) {
            $qb->leftJoin('c.parent', 'p')
                ->andWhere('p.slug = :parentSlug')
                ->setParameter('parentSlug', $parentSlug);
        }

        $qb->andWhere('c.enabled = :enabled')
                ->setParameter('enabled', true);

        if ($slug !== null) {
            $qb->andWhere('c.slug = :slug')
                ->setParameter('slug', $slug);
        } else {
            $qb->andWhere('c.parent IS NULL');
        }

        $qb->orderBy('c.ordering', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getCategoriesListByParentId($parentId = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c.id, c.title, c.slug, c.content, c.topicsCount, c.commentsCount, c.viewsCount, t.id AS topicId, t.slug AS topicSlug, t.title AS topicTitle, c.lastReplyByUsername, c.lastReplyAt, c.lastReply')
                ->leftJoin('c.lastReplyOnTopic', 't')
                ->where('c.enabled = :enabled')
                ->setParameter('enabled', true);

        if ($parentId !== null) {
            $qb->andWhere('c.parent = :parentId')
                ->setParameter('parentId', $parentId);
        }

        $qb->orderBy('c.ordering', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getEnabledCategoriesFromLowestLevelQuery()
    {
        $qb = $this->createQueryBuilder('fc')
                ->select('fc')
                ->where('a.enabled = :enabled')
                ->setParameter('enabled', true)
                ->orderBy('fc.level', 'DESC');

        return $qb->getQuery();
    }

    public function getAllCategoriesFromLowestLevelQuery()
    {
        $qb = $this->createQueryBuilder('fc')
                ->select('fc')
                ->orderBy('fc.level', 'DESC');

        return $qb->getQuery();
    }

}
