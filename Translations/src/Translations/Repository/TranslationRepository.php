<?php

namespace Translations\Repository;

use Doctrine\ORM\EntityRepository;
//use Doctrine\ORM\Query\Expr;
use Translations\Entity\Translation;

class TranslationRepository extends EntityRepository
{

    /**
     * @param string $locale
     * @return array
     */
    public function getMessages($locale)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.key, t.group, ti.locale, t.defaultValue, ti.value')
            ->join('t.values', 'ti')//, Expr\Join::WITH, 'ti.translation = t.id')
            ->where('ti.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t.group')
            ->orderBy('t.group', 'ASC');

        $return = [];
        foreach ($qb->getQuery()->getArrayResult() as $r) {
            $return[$r['group']] = $r['group'];
        }

        return $return;
    }

    /**
     * Get object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Translation
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

}
