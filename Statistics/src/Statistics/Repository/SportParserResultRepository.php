<?php

namespace Statistics\Repository;

use Doctrine\ORM\EntityRepository;
use Statistics\Entity\SportParserResult;

class SportParserResultRepository extends EntityRepository
{

    /**
     * Get SportParserResult object by key value.
     * 
     * @access public
     * @param string $key
     * @return null|SportParserResult
     */
    public function findObjectByKey($key = null)
    {
        if ($key === null) {
            return null;
        }

        $qb = $this->createQueryBuilder('t')
                ->select('t')
                ->andWhere('t.key = :key')
                ->setParameter('key', $key)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
