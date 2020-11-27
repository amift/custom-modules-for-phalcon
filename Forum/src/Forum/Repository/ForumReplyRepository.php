<?php

namespace Forum\Repository;

use Doctrine\ORM\EntityRepository;
use Forum\Entity\ForumReply;

class ForumReplyRepository extends EntityRepository
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

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('c')
                ->select('COUNT(c.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getTopicCommentsQueryBuilder($topicId)
    {
        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.topic = :topicId')
                ->setParameter('topicId', $topicId)
                ->andWhere('c.blocked = :blocked')
                ->setParameter('blocked', false);

        return $qb;
    }

    public function getTopicCommentsQuery($topicId, $ordering = 'ASC', $page = 1, $perPage = 20)
    {
        $qb = $this->getTopicCommentsQueryBuilder($topicId);

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

    public function getTopicComments($topicId, $ordering = 'ASC', $page = 1, $perPage = 20)
    {
        $qb = $this->getTopicCommentsQueryBuilder($topicId);

        if (in_array($ordering, ['ASC', 'DESC'])) {
            $qb->orderBy('c.id', $ordering);
        } else if ($ordering == 'RATED') {
            $qb->orderBy('c.rateAvg', 'DESC');
        } else {
            $qb->orderBy('c.id', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getVisibleTotalCountByTopic($topicId = null)
    {
        if ($topicId === null) {
            return 0;
        }

        $qb = $this->createQueryBuilder('c')
                ->select('COUNT(c.id) AS cnt')
                ->where('c.topic = :topicId')
                ->setParameter('topicId', $topicId)
                ->andWhere('c.blocked = :blocked')
                ->setParameter('blocked', false);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLastReplyByTopic($topicId = null)
    {
        if ($topicId === null) {
            return null;
        }

        $qb = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.topic = :topicId')
                ->setParameter('topicId', $topicId)
                ->andWhere('c.blocked = :blocked')
                ->setParameter('blocked', false)
                ->orderBy('c.createdAt', 'DESC')
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
