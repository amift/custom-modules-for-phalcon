<?php

namespace Users\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\StateChangedAtEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Users\Entity\FailedLogin;
use Users\Entity\SuccessLogin;
use Users\Tool\State;
use Users\Traits\LogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Users\Repository\UserRepository")
 * @ORM\Table(
 *      name="users",
 *      indexes={
 *          @ORM\Index(name="users_state_idx", columns={"state"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="users_email_unique_idx", columns={"email"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class User 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use StateChangedAtEntityTrait;
    use LogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="state", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = State::STATE_ACTIVE;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=50, nullable=false)
     */
    private $email = '';

    /**
     * @var string
     * @ORM\Column(name="phone", type="string", length=20, nullable=false)
     */
    private $phone = '';

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
     */
    private $firstName = '';

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=50, nullable=true)
     */
    private $lastName = '';

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=128, nullable=false)
     */
    private $password = '';

    /**
     * @var smallint
     * @ORM\Column(name="login_fail_counter", type="smallint", nullable=false)
     */
    private $loginFailCount = 0;

    /**
     * @var string
     * @ORM\Column(name="login_last_ip", type="string", length=15, nullable=true)
     */
    private $loginLastIp = '';

    /**
     * @var \DateTime
     * @ORM\Column(name="login_last_date", type="datetime", nullable=true)
     */
    private $loginLastAt = null;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Users\Entity\SuccessLogin", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $successLogins;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Users\Entity\FailedLogin", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $failedLogins;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(State::STATE_ACTIVE);
        $this->setLoginFailCount(0);
        $this->successLogins = new ArrayCollection();
        $this->failedLogins = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $displayName = sprintf('%s %s', $this->firstName, $this->lastName);

        // User will always have an email, so we do not have to throw error
        if (null === $displayName || trim($displayName) == '') {
            $displayName = substr($this->email, 0, strpos($this->email, '@'));
        }

        return $displayName;
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
     * Set status
     *
     * @param integer $state
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
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

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set loginFailCount
     *
     * @param integer $loginFailCount
     * @return User
     */
    public function setLoginFailCount($loginFailCount)
    {
        $this->loginFailCount = $loginFailCount;

        return $this;
    }

    /**
     * Get loginFailCount
     *
     * @return integer
     */
    public function getLoginFailCount()
    {
        return $this->loginFailCount;
    }

    /**
     * Set loginLastIp
     *
     * @param string $loginLastIp
     * @return User
     */
    public function setLoginLastIp($loginLastIp)
    {
        $this->loginLastIp = $loginLastIp;

        return $this;
    }

    /**
     * Get loginLastIp
     *
     * @return string
     */
    public function getLoginLastIp()
    {
        return $this->loginLastIp;
    }

    /**
     * Set loginLastAt
     *
     * @param \DateTime $loginLastAt
     * @return User
     */
    public function setLoginLastAt($loginLastAt)
    {
        $this->loginLastAt = $loginLastAt;

        return $this;
    }

    /**
     * Get loginLastAt
     *
     * @return \DateTime
     */
    public function getLoginLastAt()
    {
        return $this->loginLastAt;
    }

    /**
     * Set successLogins
     * 
     * @param $successLogins
     * @return User
     */
    public function setSuccessLogins($successLogins) 
    {
        $this->successLogins = $successLogins;

        return $this;
    }

    /**
     * Get successLogins
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuccessLogins() 
    {
        return $this->successLogins;
    }

    /**
     * Add SuccessLogin
     * 
     * @param \Users\Entity\SuccessLogin $successLogin
     */
    public function addSuccessLogin(SuccessLogin $successLogin)
    {
        if (! $this->successLogins->contains($successLogin)) {
            $this->successLogins->add($successLogin);
            $successLogin->setUser($this);
        }
    }

    /**
     * Set failedLogins
     * 
     * @param $failedLogins
     * @return User
     */
    public function setFailedLogins($failedLogins) 
    {
        $this->failedLogins = $failedLogins;

        return $this;
    }

    /**
     * Get failedLogins
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFailedLogins() 
    {
        return $this->failedLogins;
    }

    /**
     * Add FailedLogin
     * 
     * @param \Users\Entity\FailedLogin $failedLogin
     */
    public function addFailedLogin(FailedLogin $failedLogin)
    {
        if (! $this->failedLogins->contains($failedLogin)) {
            $this->failedLogins->add($failedLogin);
            $failedLogin->setUser($this);
        }
    }

}