<?php

namespace Polls\Repository;

use Doctrine\ORM\EntityRepository;
use Polls\Entity\PollVote;

class PollVoteRepository extends EntityRepository
{

    public function allreadyVoted($pollId, $memberId)
    {
        return $this->getMemberVotesCountForPoll($pollId, $memberId) > 0;
    }

    public function getMemberVotesCountForPoll($pollId, $memberId)
    {
        $qb = $this->createQueryBuilder('pv')
                ->select('COUNT(pv.id) AS cnt')
                ->where('pv.poll = :pollId')
                ->setParameter('pollId', $pollId)
                ->andWhere('pv.pollOption IS NOT NULL')
                ->andWhere('pv.member = :memberId')
                ->setParameter('memberId', $memberId);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getMemberVotedPollOption($pollId, $memberId)
    {
        $qb = $this->createQueryBuilder('pv')
                ->select('pv, po')
                ->leftJoin('pv.pollOption', 'po')
                ->where('pv.poll = :pollId')
                ->setParameter('pollId', $pollId)
                ->andWhere('pv.member = :memberId')
                ->setParameter('memberId', $memberId)
                ->setMaxResults(1)
                ->orderBy('pv.id', 'DESC');

        return $qb->getQuery()->getOneOrNullResult();
    }

}
