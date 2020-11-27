<?php

namespace Forum\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\CreatedFromIpEntityTrait;
use Common\Traits\ContentEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateTotalEntityTrait;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Forum\Tool\ForumTopicState;
use Forum\Traits\ForumLastReplyEntityTrait;
use Forum\Traits\ForumTopicCategoriesEntityTrait;
use Forum\Traits\ForumTopicLogicAwareTrait;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumTopicRepository")
 * @ORM\Table(
 *      name="forum_topics",
 *      indexes={
 *          @ORM\Index(name="forum_topics_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="forum_topics_state_idx", columns={"state"}),
 *          @ORM\Index(name="forum_topics_pinned_idx", columns={"pinned"}),
 *          @ORM\Index(name="forum_topics_locked_idx", columns={"locked"}),
 *          @ORM\Index(name="forum_topics_hot_idx", columns={"hot"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ForumTopic 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use CreatedFromIpEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use ContentEntityTrait;
    use ForumTopicCategoriesEntityTrait;
    use ForumLastReplyEntityTrait;
    use RateTotalEntityTrait;
    use ForumTopicLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member", inversedBy="articles")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = ForumTopicState::STATE_NEW;

    /**
     * @var boolean
     * @ORM\Column(name="`pinned`", type="boolean", nullable=false, options={"default":false})
     */
    private $pinned = false;

    /**
     * @var boolean
     * @ORM\Column(name="`locked`", type="boolean", nullable=false, options={"default":false})
     */
    private $locked = false;

    /**
     * @var boolean
     * @ORM\Column(name="`hot`", type="boolean", nullable=false, options={"default":false})
     */
    private $hot = false;

    /**
     * @var integer
     * @ORM\Column(name="comments_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $commentsCount;

    /**
     * @var integer
     * @ORM\Column(name="views_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $viewsCount;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(ForumTopicState::STATE_NEW);
        $this->setPinned(false);
        $this->setLocked(false);
        $this->setHot(false);
        $this->setCommentsCount(0);
        $this->setViewsCount(0);
        $this->setRateAvg(0);
        $this->setRatePlus(0);
        $this->setRateMinus(0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->title);
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
     * Set member
     * 
     * @param Member $member
     * @return ForumTopic
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
     * Set status
     *
     * @param smallint $state
     * @return ForumTopic
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get status
     *
     * @return smallint
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set pinned
     * 
     * @param boolean $pinned
     * @return ForumTopic
     */
    public function setPinned($pinned)
    {
        $this->pinned = $pinned;

        return $this;
    }

    /**
     * Get pinned
     * 
     * @return boolean 
     */
    public function getPinned()
    {
        return $this->pinned ? 1 : 0;
    }

    /**
     * Check if is pinned
     * 
     * @return boolean 
     */
    public function isPinned()
    {
        return $this->pinned === true ? true : false;
    }

    /**
     * Set locked
     * 
     * @param boolean $locked
     * @return ForumTopic
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     * 
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked ? 1 : 0;
    }

    /**
     * Check if is locked
     * 
     * @return boolean 
     */
    public function isLocked()
    {
        return $this->locked === true ? true : false;
    }

    /**
     * Set hot
     * 
     * @param boolean $hot
     * @return ForumTopic
     */
    public function setHot($hot)
    {
        $this->hot = $hot;

        return $this;
    }

    /**
     * Get hot
     * 
     * @return boolean 
     */
    public function getHot()
    {
        return $this->hot ? 1 : 0;
    }

    /**
     * Check if is hot
     * 
     * @return boolean 
     */
    public function isHot()
    {
        return $this->hot === true ? true : false;
    }

    /**
     * Set commentsCount
     * 
     * @param integer $commentsCount
     * @return ForumTopic
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;

        return $this;
    }

    /**
     * Get commentsCount
     * 
     * @return integer 
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * Set viewsCount
     * 
     * @param integer $viewsCount
     * @return ForumTopic
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;

        return $this;
    }

    /**
     * Get viewsCount
     * 
     * @return integer 
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

}
