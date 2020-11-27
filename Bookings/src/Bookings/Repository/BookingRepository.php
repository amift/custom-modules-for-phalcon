<?php

namespace Bookings\Repository;

use Bookings\Entity\Booking;
use Doctrine\ORM\EntityRepository;

class BookingRepository extends EntityRepository
{

    /**
     * Get booking object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Booking
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('b')
                ->select('b')
                ->where('b.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('b')
                ->select('COUNT(b.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

}
