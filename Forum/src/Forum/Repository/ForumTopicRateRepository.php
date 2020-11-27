<?php

namespace Forum\Repository;

use Doctrine\ORM\EntityRepository;
use Forum\Entity\ForumTopicRate;

class ForumTopicRateRepository extends EntityRepository
{

    public function allreadyRated($topicId, $memberId)
    {
        return $this->getMemberRatesCountForTopic($topicId, $memberId) > 0;
    }

    public function getMemberRatesCountForTopic($topicId, $memberId)
    {
        $qb = $this->createQueryBuilder('ar')
                ->select('COUNT(ar.id) AS cnt')
                ->where('ar.topic = :topicId')
                ->setParameter('topicId', $topicId)
                ->andWhere('ar.member = :memberId')
                ->setParameter('memberId', $memberId);

        return $qb->getQuery()->getSingleScalarResult();
    }

}
