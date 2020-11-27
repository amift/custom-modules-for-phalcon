<?php

namespace Forum\Repository;

use Doctrine\ORM\EntityRepository;
use Forum\Entity\ForumReplyRate;

class ForumReplyRateRepository extends EntityRepository
{

    public function allreadyRated($replyId, $memberId)
    {
        return $this->getMemberRatesCountForReply($replyId, $memberId) > 0;
    }

    public function getMemberRatesCountForReply($replyId, $memberId)
    {
        $qb = $this->createQueryBuilder('cr')
                ->select('COUNT(cr.id) AS cnt')
                ->where('cr.reply = :replyId')
                ->setParameter('replyId', $replyId)
                ->andWhere('cr.member = :memberId')
                ->setParameter('memberId', $memberId);

        return $qb->getQuery()->getSingleScalarResult();
    }

}
