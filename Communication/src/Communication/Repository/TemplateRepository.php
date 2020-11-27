<?php

namespace Communication\Repository;

use Doctrine\ORM\EntityRepository;
use Communication\Entity\Template;

class TemplateRepository extends EntityRepository
{

    /**
     * Get template object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Template
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('t')
                ->select('t')
                ->where('t.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get template by code
     * 
     * @access public
     * @return null|Template
     */
    public function getTemplate($code)
    {
        $query = $this->createQueryBuilder('t')
                ->where('t.code = :code')
                ->setParameter(':code', $code);

        return $query->getQuery()->getSingleResult();
    }

}
