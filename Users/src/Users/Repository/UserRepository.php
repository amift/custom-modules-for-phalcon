<?php

namespace Users\Repository;

use Doctrine\ORM\EntityRepository;
use Users\Entity\User;

class UserRepository extends EntityRepository
{

    /**
     * Get user object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|User
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('u')
                ->select('u')
                ->where('u.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get user object by Email value.
     * 
     * @access public
     * @param string $email
     * @return null|User
     */
    public function findObjectByEmail($email = null)
    {
        $qb = $this->createQueryBuilder('u')
                ->select('u')
                ->where('u.email = :email')
                ->setParameter('email', $email)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
