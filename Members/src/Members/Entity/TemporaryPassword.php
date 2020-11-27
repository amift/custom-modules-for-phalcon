<?php

namespace Members\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\CreatedFromIpEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Members\Repository\TemporaryPasswordRepository")
 * @ORM\Table(name="members_temporary_passwords")
 * @ORM\HasLifecycleCallbacks
 */
class TemporaryPassword 
{
    use CreatedAtEntityTrait;
    use CreatedFromIpEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $member;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=128, nullable=false)
     */
    private $password = '';

    /**
     * Class constructor
     */
    public function __construct() 
    {
        //
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
     * @return TemporaryPassword
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
     * Set password
     *
     * @param string $password
     * @return TemporaryPassword
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

}
