<?php

namespace Communication\Repository;

use Doctrine\ORM\EntityRepository;
use Communication\Entity\Newsletter;
use Communication\Tool\NewsletterState;

class NewsletterRepository extends EntityRepository
{
    /**
     * Get newsletter object by ID value.
     *
     * @access public
     * @param int $id
     * @return null|Newsletter
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('n')
                ->select('n')
                ->where('n.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get queued newsletters
     *
     * @access public
     * @param \DateTime $currentTime
     * @param int $limit
     * @return \Doctrine\ORM\Query
     */
    public function getQueuedNewslettersQuery($currentTime, $limit = 10)
    {
        $qb = $this->createQueryBuilder('n')
                ->select('n')
                ->where('n.state = :state')
                ->setParameter('state', NewsletterState::QUEUED)
                ->andWhere('n.toSendAt <= :toSendAt')
                ->setParameter(':toSendAt', $currentTime->format('Y-m-d H:i:s'))
                ->orderBy('n.toSendAt', 'ASC')
                ->setMaxResults($limit);

        return $qb->getQuery();
    }
}