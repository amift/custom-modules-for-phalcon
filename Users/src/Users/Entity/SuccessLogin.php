<?php

namespace Users\Entity;

use Common\Traits\LoginDataEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Users\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_success_logins")
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
     * @var User
     * @ORM\ManyToOne(targetEntity="Users\Entity\User", inversedBy="successLogins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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
     * @return SuccessLogins
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

}
