<?php

namespace Members\Entity;

use Articles\Entity\Article;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\CreatedFromIpEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\StateChangedAtEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\FailedLogin;
use Members\Entity\SuccessLogin;
use Members\Entity\TotalPoints;
use Members\Tool\State;
use Members\Traits\LogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Members\Repository\MemberRepository")
 * @ORM\Table(
 *      name="members",
 *      indexes={
 *          @ORM\Index(name="members_email_idx", columns={"email"}),
 *          @ORM\Index(name="members_confirmed_idx", columns={"confirmed"}),
 *          @ORM\Index(name="members_state_idx", columns={"state"}),
 *          @ORM\Index(name="members_confirm_code_idx", columns={"confirm_code"}),
 *          @ORM\Index(name="members_newsletter_custom_idx", columns={"newsletter_custom"}),
 *          @ORM\Index(name="members_newsletter_weekly_idx", columns={"newsletter_weekly"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="members_email_unique_idx", columns={"email"}),
 *          @ORM\UniqueConstraint(name="members_username_unique_idx", columns={"username"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Member 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use CreatedFromIpEntityTrait;
    use StateChangedAtEntityTrait;
    use LogicAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=30, nullable=false)
     */
    private $username = '';

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=75, nullable=false)
     */
    private $email = '';

    /**
     * @var boolean
     * @ORM\Column(name="confirmed", type="boolean", nullable=false, options={"default":false})
     */
    private $confirmed = false;

    /**
     * @var smallint
     * @ORM\Column(name="state", type="smallint", options={"unsigned":true})
     */
    private $state = State::STATE_ACTIVE;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=128, nullable=false)
     */
    private $password = '';

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
     * @ORM\OneToMany(targetEntity="Members\Entity\SuccessLogin", mappedBy="member", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $successLogins;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Members\Entity\FailedLogin", mappedBy="member", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $failedLogins;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Article", mappedBy="member", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $articles;

    /**
     * @var TotalPoints
     * @ORM\OneToOne(targetEntity="Members\Entity\TotalPoints", mappedBy="member", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $totalPointsData;

    /**
     * @var string
     * @ORM\Column(name="confirm_code", type="string", length=50, nullable=true, options={"default":null})
     */
    private $confirmCode = '';

    /**
     * @var \DateTime
     * @ORM\Column(name="confirmed_at", type="datetime", nullable=true, options={"default":null})
     */
    private $confirmedAt;

    /**
     * @var boolean
     * @ORM\Column(name="banned_posting", type="boolean", nullable=false, options={"default":false})
     */
    private $bannedPosting = false;

    /**
     * @var boolean
     * @ORM\Column(name="banned_commenting", type="boolean", nullable=false, options={"default":false})
     */
    private $bannedCommenting = false;

    /**
     * @var boolean
     * @ORM\Column(name="banned_forum_topics", type="boolean", nullable=false, options={"default":false})
     */
    private $bannedForumTopics = false;

    /**
     * @var boolean
     * @ORM\Column(name="banned_forum_replies", type="boolean", nullable=false, options={"default":false})
     */
    private $bannedForumReplies = false;

    /**
     * @var boolean
     * @ORM\Column(name="newsletter_custom", type="boolean", nullable=false, options={"default":true})
     */
    private $newsletterCustom = true;

    /**
     * @var boolean
     * @ORM\Column(name="newsletter_weekly", type="boolean", nullable=false, options={"default":true})
     */
    private $newsletterWeekly = true;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setConfirmed(false);
        $this->setState(State::STATE_ACTIVE);
        $this->articles = new ArrayCollection();
        $this->successLogins = new ArrayCollection();
        $this->failedLogins = new ArrayCollection();
        $this->setBannedPosting(false);
        $this->setBannedCommenting(false);
        $this->setNewsletterCustom(true);
        $this->setNewsletterWeekly(true);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $displayName = sprintf('%s', $this->username);

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
     * Set username
     *
     * @param string $username
     * @return Member
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Member
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
     * Set confirmed
     * 
     * @param boolean $confirmed
     * @return Member
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     * 
     * @return boolean 
     */
    public function getConfirmed()
    {
        return $this->confirmed ? 1 : 0;
    }

    /**
     * Set status
     *
     * @param integer $state
     * @return Member
     */
    public function setState($state)
    {
        $this->state = (int)$state;

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
     * Set password
     *
     * @param string $password
     * @return Member
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
     * Set loginLastIp
     *
     * @param string $loginLastIp
     * @return Member
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
     * @return Member
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
     * @return Member
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
     * @param \Members\Entity\SuccessLogin $successLogin
     */
    public function addSuccessLogin(SuccessLogin $successLogin)
    {
        if (! $this->successLogins->contains($successLogin)) {
            $this->successLogins->add($successLogin);
            $successLogin->setMember($this);
        }
    }

    /**
     * Set failedLogins
     * 
     * @param $failedLogins
     * @return Member
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
     * @param \Members\Entity\FailedLogin $failedLogin
     */
    public function addFailedLogin(FailedLogin $failedLogin)
    {
        if (! $this->failedLogins->contains($failedLogin)) {
            $this->failedLogins->add($failedLogin);
            $failedLogin->setMember($this);
        }
    }

    /**
     * Set articles
     * 
     * @param $articles
     * @return Member
     */
    public function setArticles($articles) 
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Get articles
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles() 
    {
        return $this->articles;
    }

    /**
     * Add Article
     * 
     * @param \Articles\Entity\Article $article
     */
    public function addArticle(Article $article)
    {
        if (! $this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setMember($this);
        }
    }

    /**
     * Set totalPointsData
     * 
     * @param TotalPoints $totalPointsData
     * @return Member
     */
    public function setTotalPointsData(TotalPoints $totalPointsData)
    {
        $this->totalPointsData = $totalPointsData;

        return $this;
    }

    /**
     * Get totalPointsData
     * 
     * @return TotalPoints
     */
    public function getTotalPointsData()
    {
        return $this->totalPointsData;
    }

    /**
     * Set confirmCode
     *
     * @param string $confirmCode
     * @return Member
     */
    public function setConfirmCode($confirmCode)
    {
        $this->confirmCode = $confirmCode;

        return $this;
    }

    /**
     * Get confirmCode
     *
     * @return string
     */
    public function getConfirmCode()
    {
        return $this->confirmCode;
    }

    /**
     * Set confirmedAt
     *
     * @param \DateTime $confirmedAt
     * @return Member
     */
    public function setConfirmedAt($confirmedAt)
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * Get confirmedAt
     *
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * Set bannedPosting
     * 
     * @param boolean $bannedPosting
     * @return Member
     */
    public function setBannedPosting($bannedPosting)
    {
        $this->bannedPosting = (bool)$bannedPosting;

        return $this;
    }

    /**
     * Get bannedPosting
     * 
     * @return boolean 
     */
    public function getBannedPosting()
    {
        return $this->bannedPosting ? 1 : 0;
    }

    /**
     * Check if is bannedPosting
     * 
     * @return boolean 
     */
    public function isBannedPosting()
    {
        return $this->bannedPosting ? true : false;
    }

    /**
     * Set bannedCommenting
     * 
     * @param boolean $bannedCommenting
     * @return Member
     */
    public function setBannedCommenting($bannedCommenting)
    {
        $this->bannedCommenting = $bannedCommenting;

        return $this;
    }

    /**
     * Get bannedCommenting
     * 
     * @return boolean 
     */
    public function getBannedCommenting()
    {
        return $this->bannedCommenting ? 1 : 0;
    }

    /**
     * Check if is bannedCommenting
     * 
     * @return boolean 
     */
    public function isBannedCommenting()
    {
        return $this->bannedCommenting ? true : false;
    }

    /**
     * Set bannedForumTopics
     * 
     * @param boolean $bannedForumTopics
     * @return Member
     */
    public function setBannedForumTopics($bannedForumTopics)
    {
        $this->bannedForumTopics = $bannedForumTopics;

        return $this;
    }

    /**
     * Get bannedForumTopics
     * 
     * @return boolean 
     */
    public function getBannedForumTopics()
    {
        return $this->bannedForumTopics ? 1 : 0;
    }

    /**
     * Check if is bannedForumTopics
     * 
     * @return boolean 
     */
    public function isBannedForumTopics()
    {
        return $this->bannedForumTopics ? true : false;
    }

    /**
     * Set bannedForumReplies
     * 
     * @param boolean $bannedForumReplies
     * @return Member
     */
    public function setBannedForumReplies($bannedForumReplies)
    {
        $this->bannedForumReplies = $bannedForumReplies;

        return $this;
    }

    /**
     * Get bannedForumReplies
     * 
     * @return boolean 
     */
    public function getBannedForumReplies()
    {
        return $this->bannedForumReplies ? 1 : 0;
    }

    /**
     * Check if is bannedForumReplies
     * 
     * @return boolean 
     */
    public function isBannedForumReplies()
    {
        return $this->bannedForumReplies ? true : false;
    }

    /**
     * Set newsletterCustom
     * 
     * @param boolean $newsletterCustom
     * @return Member
     */
    public function setNewsletterCustom($newsletterCustom)
    {
        $this->newsletterCustom = $newsletterCustom;

        return $this;
    }

    /**
     * Get newsletterCustom
     * 
     * @return boolean 
     */
    public function getNewsletterCustom()
    {
        return $this->newsletterCustom ? 1 : 0;
    }

    /**
     * Check if is newsletterCustom
     * 
     * @return boolean 
     */
    public function isNewsletterCustom()
    {
        return $this->newsletterCustom ? true : false;
    }

    /**
     * Set newsletterWeekly
     * 
     * @param boolean $newsletterWeekly
     * @return Member
     */
    public function setNewsletterWeekly($newsletterWeekly)
    {
        $this->newsletterWeekly = $newsletterWeekly;

        return $this;
    }

    /**
     * Get newsletterWeekly
     * 
     * @return boolean 
     */
    public function getNewsletterWeekly()
    {
        return $this->newsletterWeekly ? 1 : 0;
    }

    /**
     * Check if is newsletterWeekly
     * 
     * @return boolean 
     */
    public function isNewsletterWeekly()
    {
        return $this->newsletterWeekly ? true : false;
    }
}