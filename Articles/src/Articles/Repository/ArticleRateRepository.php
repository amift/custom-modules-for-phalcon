<?php

namespace Articles\Repository;

use Doctrine\ORM\EntityRepository;
use Articles\Entity\ArticleRate;

class ArticleRateRepository extends EntityRepository
{

    public function allreadyRated($articleId, $memberId)
    {
        return $this->getMemberRatesCountForArticle($articleId, $memberId) > 0;
    }

    public function getMemberRatesCountForArticle($articleId, $memberId)
    {
        $qb = $this->createQueryBuilder('ar')
                ->select('COUNT(ar.id) AS cnt')
                ->where('ar.article = :articleId')
                ->setParameter('articleId', $articleId)
                ->andWhere('ar.member = :memberId')
                ->setParameter('memberId', $memberId);

        return $qb->getQuery()->getSingleScalarResult();
    }

}
