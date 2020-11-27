<?php

namespace Members\Entity;

use Common\Traits\LoginDataEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity
 * @ORM\Table(name="members_success_logins")
 */
class SuccessLogin 
{

    use LoginDataEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member", inversedBy="successLogins")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $member;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->setDate(new \DateTime('now'));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set related member object 
     *
     * @param Member $member
     * @return SuccessLogin
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get related member object 
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

}
