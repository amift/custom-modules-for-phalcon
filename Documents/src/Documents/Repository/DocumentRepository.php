<?php

namespace Documents\Repository;

use Doctrine\ORM\EntityRepository;
use Documents\Entity\Document;

class DocumentRepository extends EntityRepository
{

    /**
     * Get document object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Document
     */
    public function findObjectById($id = null)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m')
                ->where('m.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get document object by KEY value.
     * 
     * @access public
     * @param string $key
     * @return null|Document
     */
    public function findObjectByKey($key = null)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m')
                ->where('m.key = :key')
                ->setParameter('key', $key)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get document object by SLUG value.
     * 
     * @access public
     * @param string $slug
     * @return null|Document
     */
    public function findObjectBySlug($slug = null)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m')
                ->where('m.slug = :slug')
                ->setParameter('slug', $slug)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getParentsList()
    {
        $qb = $this->createQueryBuilder('d')
                ->select('d.id, p.id AS parent, d.title, d.slug')
                ->leftJoin('d.parent', 'p')
                ->orderBy('d.parent', 'DESC')
                ->addOrderBy('d.id', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }

}
