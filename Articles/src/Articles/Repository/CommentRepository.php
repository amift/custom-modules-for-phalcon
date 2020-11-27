<?php

namespace Articles\Repository;

use Doctrine\ORM\EntityRepository;
use Articles\Entity\Comment;

class CommentRepository extends EntityRepository
{

    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getArticleCommentsQueryBuilder($articleId)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.article = :articleId')
                ->setParameter('articleId', $articleId)
                ->andWhere('c.blocked = :blocked')
                ->setParameter('blocked', false);

        return $qb;
    }

    public function getArticleCommentsQuery($articleId, $ordering = 'ASC', $page = 1, $perPage = 10)
    {
        $qb = $this->getArticleCommentsQueryBuilder($articleId);

        $qb->setFirstResult(($page - 1) * $perPage);
        $qb->setMaxResults($perPage);

        if (in_array($ordering, ['ASC', 'DESC'])) {
            $qb->orderBy('c.id', $ordering);
        } else {
            if ($ordering == 'RATED') {
                $qb->addOrderBy('c.rateAvg', 'DESC');
            } else {            
                $qb->orderBy('c.id', 'ASC');
            }
        }

        return $qb->getQuery();
    }

    public function getArticleComments($articleId, $ordering = 'ASC', $page = 1, $perPage = 20)
    {
        $qb = $this->getArticleCommentsQueryBuilder($articleId);

        if (in_array($ordering, ['ASC', 'DESC'])) {
            $qb->orderBy('c.id', $ordering);
        } else if ($ordering == 'RATED') {
            $qb->orderBy('c.rateAvg', 'DESC');
        } else {
            $qb->orderBy('c.id', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getPollCommentsQueryBuilder($pollId)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.poll = :pollId')
                ->setParameter('pollId', $pollId)
                ->andWhere('c.blocked = :blocked')
                ->setParameter('blocked', false);

        return $qb;
    }

    public function getPollCommentsQuery($pollId, $ordering = 'ASC', $page = 1, $perPage = 10)
    {
        $qb = $this->getPollCommentsQueryBuilder($pollId);

        $qb->setFirstResult(($page - 1) * $perPage);
        $qb->setMaxResults($perPage);

        if (in_array($ordering, ['ASC', 'DESC'])) {
            $qb->orderBy('c.id', $ordering);
        } else {
            if ($ordering == 'RATED') {
                $qb->addOrderBy('c.rateAvg', 'DESC');
            } else {            
                $qb->orderBy('c.id', 'ASC');
            }
        }

        return $qb->getQuery();
    }

    public function getPollComments($pollId, $ordering = 'ASC', $page = 1, $perPage = 20)
    {
        $qb = $this->getPollCommentsQueryBuilder($pollId);

        if (in_array($ordering, ['ASC', 'DESC'])) {
            $qb->orderBy('c.id', $ordering);
        } else if ($ordering == 'RATED') {
            $qb->orderBy('c.rateAvg', 'DESC');
        } else {
            $qb->orderBy('c.id', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('c')
                ->select('COUNT(c.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

}
