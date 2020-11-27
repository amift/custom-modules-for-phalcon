<?php

namespace Members\Entity;

use Common\Traits\LoginDataEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity
 * @ORM\Table(name="members_failed_logins")
 */
class FailedLogin 
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
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member", inversedBy="failedLogins")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $member;

    /**
     * @var string
     * @ORM\Column(name="user_email", type="string", length=50, nullable=false)
     */
    private $userEmail = '';

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
     * @return FailedLogin
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

    /**
     * Set userEmail
     *
     * @param string $userEmail
     * @return FailedLogin
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    /**
     * Get userEmail
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

}
