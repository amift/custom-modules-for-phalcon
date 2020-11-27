<?php

namespace Members\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity
 * @ORM\Table(name="members_total_points")
 * @ORM\HasLifecycleCallbacks
 */
class TotalPoints 
{

    use ObjectSimpleHydrating;

    /**
     * @var Member
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Members\Entity\Member", inversedBy="totalPointsData")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $member;

    /**
     * @var integer
     * @ORM\Column(name="total_earned", type="integer", options={"unsigned":true})
     */
    private $totalEarned;

    /**
     * @var integer
     * @ORM\Column(name="total_withdrawed", type="integer", options={"unsigned":true})
     */
    private $totalWithdrawed;

    /**
     * @var integer
     * @ORM\Column(name="total_actual", type="integer", options={"unsigned":true})
     */
    private $totalActual;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setTotalEarned(0);
        $this->setTotalWithdrawed(0);
        $this->setTotalActual(0);
    }

    /**
     * Set member 
     *
     * @param Member $member
     * @return TotalPoints
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set totalEarned 
     *
     * @param int $totalEarned
     * @return TotalPoints
     */
    public function setTotalEarned($totalEarned)
    {
        $this->totalEarned = $totalEarned;

        return $this;
    }

    /**
     * Get totalEarned
     *
     * @return int
     */
    public function getTotalEarned()
    {
        return $this->totalEarned;
    }

    /**
     * Set totalWithdrawed 
     *
     * @param int $totalWithdrawed
     * @return TotalPoints
     */
    public function setTotalWithdrawed($totalWithdrawed)
    {
        $this->totalWithdrawed = $totalWithdrawed;

        return $this;
    }

    /**
     * Get totalWithdrawed
     *
     * @return int
     */
    public function getTotalWithdrawed()
    {
        return $this->totalWithdrawed;
    }

    /**
     * Set totalActual 
     *
     * @param int $totalActual
     * @return TotalPoints
     */
    public function setTotalActual($totalActual)
    {
        $this->totalActual = $totalActual;

        return $this;
    }

    /**
     * Get totalActual
     *
     * @return int
     */
    public function getTotalActual()
    {
        return $this->totalActual;
    }

    /**
     * Recalculate total actual points value
     */
    public function recalculateActual()
    {
        $this->totalActual = $this->totalEarned - $this->totalWithdrawed;
    }

}
