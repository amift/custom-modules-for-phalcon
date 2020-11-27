<?php

namespace Communication\Repository;

use Doctrine\ORM\EntityRepository;
use Communication\Entity\Notification;
use Communication\Tool\NotificationState as State;
use Communication\Tool\TemplateType as Type;

class NotificationRepository extends EntityRepository
{

    /**
     * Get notification object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Notification
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
     * Get scheduled email notifications
     * 
     * @access public
     * @param int $limit
     * @param \DateTime $currentTime
     * @return \Doctrine\ORM\Query
     */
    public function getScheduledEmailNotificationsQuery($limit, $currentTime)
    {
        $qb = $this->createQueryBuilder('n')
                ->select('n')
                ->where('n.state = :state')
                ->setParameter('state', State::STATUS_NEW)
                ->andWhere('n.type = :type')
                ->setParameter('type', Type::TYPE_EMAIL)
                ->andWhere('n.toSendAt <= :toSendAt')
                ->setParameter(':toSendAt', $currentTime->format('Y-m-d H:i:s'))
                ->orderBy('n.id', 'ASC')
                ->setMaxResults($limit);

        return $qb->getQuery();
    }

}
