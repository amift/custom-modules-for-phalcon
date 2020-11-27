<?php

namespace Articles\Repository;

use Doctrine\ORM\EntityRepository;
use Articles\Entity\CommentRate;

class CommentRateRepository extends EntityRepository
{

    public function allreadyRated($commentId, $memberId)
    {
        return $this->getMemberRatesCountForComment($commentId, $memberId) > 0;
    }

    public function getMemberRatesCountForComment($commentId, $memberId)
    {
        $qb = $this->createQueryBuilder('cr')
                ->select('COUNT(cr.id) AS cnt')
                ->where('cr.comment = :commentId')
                ->setParameter('commentId', $commentId)
                ->andWhere('cr.member = :memberId')
                ->setParameter('memberId', $memberId);

        return $qb->getQuery()->getSingleScalarResult();
    }


}
