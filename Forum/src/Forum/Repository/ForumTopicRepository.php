<?php

namespace Forum\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Forum\Entity\ForumTopic;
use Forum\Tool\ForumTopicState;

class ForumTopicRepository extends EntityRepository
{

    /**
     * Get forum topic object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|ForumTopic
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

    public function getOutputTopicsQueryBuilder($categoryFieldName = null, $categoryFieldValue = null)
    {
        $qb = $this->createQueryBuilder('a')
                ->select('a, c1')
                ->leftJoin('a.categoryLvl1', 'c1')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($categoryFieldName !== null && $categoryFieldValue !== null) {
            $qb->andWhere('a.' . $categoryFieldName . ' IN (:category)')
                ->setParameter('category', $categoryFieldValue);
        }
        $qb->orderBy('a.id', 'DESC');

        return $qb;
    }

    public function getTopics($category = null, $page = 1, $perPage = 20)
    {
        if ($category === null || !is_object($category)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
                ->select('a')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($category->getLevel() < 4) {
            $categoryFieldName = 'categoryLvl'.$category->getLevel();
            $categoryFieldValue = $category->getId();
            if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                    ->setParameter($categoryFieldName, $categoryFieldValue);
            }
            
            $parentCategory = $category->getParent();
            if (is_object($parentCategory)) {
                $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                $categoryFieldValue = $parentCategory->getId();
                if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                    $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                        ->setParameter($categoryFieldName, $categoryFieldValue);
                }
                
                $parentCategory = $category->getParent();
                if (is_object($parentCategory)) {
                    $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                    $categoryFieldValue = $parentCategory->getId();
                    if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                        $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                            ->setParameter($categoryFieldName, $categoryFieldValue);
                    }
                }
            }
        }

        $qb->orderBy('a.pinned', 'DESC');
        $qb->addOrderBy('a.hot', 'DESC');
        $qb->addOrderBy('a.locked', 'ASC');
        $qb->addOrderBy('a.id', 'DESC');
        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return new Paginator($qb->getQuery(), true);
    }

    public function getMemberTopicsQuery($memberId, $page = 1, $perPage = 10)
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

    public function getVisibleTotalCountByCategory($category = null)
    {
        if ($category === null || !is_object($category)) {
            return 0;
        }

        $qb = $this->createQueryBuilder('a')
                ->select('COUNT(a.id) AS cnt')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($category->getLevel() < 4) {
            $categoryFieldName = 'categoryLvl'.$category->getLevel();
            $categoryFieldValue = $category->getId();
            if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                    ->setParameter($categoryFieldName, $categoryFieldValue);
            }
            
            $parentCategory = $category->getParent();
            if (is_object($parentCategory)) {
                $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                $categoryFieldValue = $parentCategory->getId();
                if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                    $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                        ->setParameter($categoryFieldName, $categoryFieldValue);
                }
                
                $parentCategory = $category->getParent();
                if (is_object($parentCategory)) {
                    $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                    $categoryFieldValue = $parentCategory->getId();
                    if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                        $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                            ->setParameter($categoryFieldName, $categoryFieldValue);
                    }
                }
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getVisibleTopicsByCategory($category = null)
    {
        if ($category === null || !is_object($category)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
                ->select('a')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($category->getLevel() < 4) {
            $categoryFieldName = 'categoryLvl'.$category->getLevel();
            $categoryFieldValue = $category->getId();
            if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                    ->setParameter($categoryFieldName, $categoryFieldValue);
            }
            
            $parentCategory = $category->getParent();
            if (is_object($parentCategory)) {
                $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                $categoryFieldValue = $parentCategory->getId();
                if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                    $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                        ->setParameter($categoryFieldName, $categoryFieldValue);
                }
                
                $parentCategory = $category->getParent();
                if (is_object($parentCategory)) {
                    $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                    $categoryFieldValue = $parentCategory->getId();
                    if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                        $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                            ->setParameter($categoryFieldName, $categoryFieldValue);
                    }
                }
            }
        }
        $qb->orderBy('a.lastReplyAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getLastReplyOnTopicByCategory($category = null)
    {
        if ($category === null || !is_object($category)) {
            return null;
        }

        $qb = $this->createQueryBuilder('a')
                ->select('a')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($category->getLevel() < 4) {
            $categoryFieldName = 'categoryLvl'.$category->getLevel();
            $categoryFieldValue = $category->getId();
            if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                    ->setParameter($categoryFieldName, $categoryFieldValue);
            }
            
            $parentCategory = $category->getParent();
            if (is_object($parentCategory)) {
                $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                $categoryFieldValue = $parentCategory->getId();
                if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                    $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                        ->setParameter($categoryFieldName, $categoryFieldValue);
                }
                
                $parentCategory = $category->getParent();
                if (is_object($parentCategory)) {
                    $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                    $categoryFieldValue = $parentCategory->getId();
                    if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                        $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                            ->setParameter($categoryFieldName, $categoryFieldValue);
                    }
                }
            }
        }
        $qb->orderBy('a.lastReplyAt', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAllVisibleTopicsQuery()
    {
        $qb = $this->createQueryBuilder('a')
                ->select('a')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        return $qb->getQuery();
    }

    public function getVisibleTotalRepliesSumCountByCategory($category = null)
    {
        if ($category === null || !is_object($category)) {
            return 0;
        }

        $qb = $this->createQueryBuilder('a')
                ->select('SUM(a.commentsCount) AS cnt')
                ->where('a.state IN (:states)')
                ->setParameter('states', ForumTopicState::getOutputStates());

        if ($category->getLevel() < 4) {
            $categoryFieldName = 'categoryLvl'.$category->getLevel();
            $categoryFieldValue = $category->getId();
            if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                    ->setParameter($categoryFieldName, $categoryFieldValue);
            }
            
            $parentCategory = $category->getParent();
            if (is_object($parentCategory)) {
                $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                $categoryFieldValue = $parentCategory->getId();
                if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                    $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                        ->setParameter($categoryFieldName, $categoryFieldValue);
                }
                
                $parentCategory = $category->getParent();
                if (is_object($parentCategory)) {
                    $categoryFieldName = 'categoryLvl'.$parentCategory->getLevel();
                    $categoryFieldValue = $parentCategory->getId();
                    if ($categoryFieldName !== null && $categoryFieldValue !== null) {
                        $qb->andWhere('a.' . $categoryFieldName . ' = :'.$categoryFieldName)
                            ->setParameter($categoryFieldName, $categoryFieldValue);
                    }
                }
            }
        }
        //$qb->groupBy('a.id');

        return $qb->getQuery()->getSingleScalarResult();
    }

}
