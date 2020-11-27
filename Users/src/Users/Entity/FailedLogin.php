<?php

namespace Users\Entity;

use Common\Traits\LoginDataEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Users\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_failed_logins")
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
     * @var User
     * @ORM\ManyToOne(targetEntity="Users\Entity\User", inversedBy="failedLogins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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
     * Set related user object 
     *
     * @param User $user
     * @return FailedLogin
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get related user object 
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
