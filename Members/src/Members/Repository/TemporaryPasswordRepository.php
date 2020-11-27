<?php

namespace Members\Repository;

use Doctrine\ORM\EntityRepository;
use Members\Entity\TemporaryPassword;

class TemporaryPasswordRepository extends EntityRepository
{

    public function getActualTemporaryPasswords($memberId, $minutes = 60)
    {
        if ($minutes < 1) {
            throw new \Core\Exception\InvalidArgumentException('Minutes for temporary password actuality must be more then 1 minute');
        }

        $date = new \DateTime('now');
        $date->modify('-' . $minutes . ' minute' . ($minutes > 1 ? 's' : ''));

        $qb = $this->createQueryBuilder('tmp')
                ->select('tmp.password')
                ->where('tmp.member = :memberId')
                ->setParameter('memberId', $memberId)
                ->andWhere('tmp.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $date->format('Y-m-d H:i:s'))
                ->orderBy('tmp.id', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

}
