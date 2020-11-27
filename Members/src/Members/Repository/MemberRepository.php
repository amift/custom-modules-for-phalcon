<?php

namespace Members\Repository;

use Common\Tool\PeriodTool;
use Doctrine\ORM\EntityRepository;
use Members\Entity\Member;

class MemberRepository extends EntityRepository
{

    /**
     * Get member object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Member
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
     * Get member object by Email value.
     * 
     * @access public
     * @param string $email
     * @return null|Member
     */
    public function findObjectByEmail($email = null)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m')
                ->where('m.email = :email')
                ->setParameter('email', $email)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get member object by ConfirmCode value.
     * 
     * @access public
     * @param string $code
     * @return null|Member
     */
    public function findObjectByConfirmCode($code = null)
    {
        $qb = $this->createQueryBuilder('m')
                ->select('m')
                ->where('m.confirmCode = :confirmCode')
                ->setParameter('confirmCode', $code)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        $qb = $this->createQueryBuilder('m')
                ->select('COUNT(m.id) AS cnt');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getNewsletterReceiversByCriterias($criterias = [])
    {
        $qb = $this->createQueryBuilder('m')->select('m.id, m.email, m.confirmCode');

        if (isset($criterias['confirmed']) && (string)$criterias['confirmed'] !== '') {
            $qb->andWhere('m.confirmed = :confirmed')->setParameter('confirmed', (string)$criterias['confirmed'] === '1' ? 1 : 0);
        }

        if (isset($criterias['state']) && (string)$criterias['state'] !== '') {
            $qb->andWhere('m.state = :state')->setParameter('state', (int)$criterias['state']);
        }

        if (isset($criterias['hasArticles']) && (string)$criterias['hasArticles'] !== '') {
            $func = (string)$criterias['hasArticles'] === '1' ? 'EXISTS' : 'NOT EXISTS';
            $qb->andWhere('(' . $func . ' (SELECT 1 FROM Articles\Entity\Article ha WHERE ha.member = m.id))');
        }

        if (isset($criterias['hasComments']) && (string)$criterias['hasComments'] !== '') {
            $func = (string)$criterias['hasComments'] === '1' ? 'EXISTS' : 'NOT EXISTS';
            $qb->andWhere('(' . $func . ' (SELECT 1 FROM Articles\Entity\Comment hc WHERE hc.member = m.id))');
        }

        if (isset($criterias['hasWithdraws']) && (string)$criterias['hasWithdraws'] !== '') {
            $func = (string)$criterias['hasWithdraws'] === '1' ? 'EXISTS' : 'NOT EXISTS';
            $qb->andWhere('(' . $func . ' (SELECT 1 FROM Members\Entity\Withdraws hw WHERE hw.member = m.id))');
        }

        if (isset($criterias['hasActualPoints']) && (string)$criterias['hasActualPoints'] !== '') {
            if ((string)$criterias['hasActualPoints'] === '1') {
                $qb->andWhere('(EXISTS (SELECT 1 FROM Members\Entity\TotalPoints hap WHERE hap.member = m.id AND hap.totalActual > 0))');
            } else {
                $qb->andWhere('( (EXISTS (SELECT 1 FROM Members\Entity\TotalPoints hap WHERE hap.member = m.id AND hap.totalActual = 0)) OR (NOT EXISTS (SELECT 1 FROM Members\Entity\TotalPoints hap WHERE hap.member = m.id)) )');
            }
        }

        if (isset($criterias['hasForumAct']) && (string)$criterias['hasForumAct'] !== '') {
            // 1 || 0
        }

        if (isset($criterias['lastAuthorization']) && (string)$criterias['lastAuthorization'] !== '') {

            list($dateFrom, $dateTill) = PeriodTool::periodDates((string)$criterias['lastAuthorization']);
            var_dump($dateFrom);
            var_dump($dateTill);
            die();

            //m.loginLastAt
            // 1_week, 2_week, 3_week, 1_month, 2_month, 3_month, 4_month, 5_month, 6_month, 7_month, 8_month, 9_month, 10_month, 11_month, 12_month, more_than_year
        }

        if (isset($criterias['lastArticleCreated']) && (string)$criterias['lastArticleCreated'] !== '') {

            list($dateFrom, $dateTill) = PeriodTool::periodDates((string)$criterias['lastArticleCreated']);
            var_dump($dateFrom);
            var_dump($dateTill);
            die();
            // 1_week, 2_week, 3_week, 1_month, 2_month, 3_month, 4_month, 5_month, 6_month, 7_month, 8_month, 9_month, 10_month, 11_month, 12_month, more_than_year
        }

        if (isset($criterias['lastCommentCreated']) && (string)$criterias['lastCommentCreated'] !== '') {

            list($dateFrom, $dateTill) = PeriodTool::periodDates((string)$criterias['lastCommentCreated']);
            var_dump($dateFrom);
            var_dump($dateTill);
            die();
            // 1_week, 2_week, 3_week, 1_month, 2_month, 3_month, 4_month, 5_month, 6_month, 7_month, 8_month, 9_month, 10_month, 11_month, 12_month, more_than_year
        }

        return $qb->getQuery()->getArrayResult();
    }
}
