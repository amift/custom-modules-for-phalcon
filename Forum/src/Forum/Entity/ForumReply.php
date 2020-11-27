<?php

namespace Forum\Entity;

use Common\Traits\BlockedEntityTrait;
use Common\Traits\ContentEntityTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateTotalEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Common\Traits\UncheckedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Forum\Entity\ForumTopic;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumReplyRepository")
 * @ORM\Table(
 *      name="forum_replies",
 *      indexes={
 *          @ORM\Index(name="forum_replies_blocked_idx", columns={"blocked"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ForumReply 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use ContentEntityTrait;
    use BlockedEntityTrait;
    use IpAddreesEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;
    use RateTotalEntityTrait;
    use UncheckedEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ForumTopic
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumTopic")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $topic;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumReply", inversedBy="replyComments", cascade={"persist"})
     * @ORM\JoinColumn(name="reply_on_comment_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $replyOnComment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Forum\Entity\ForumReply", mappedBy="replyOnComment")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $replyComments;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setBlocked(false);
        $this->setUnchecked(true);
        $this->setRateAvg(0);
        $this->setRatePlus(0);
        $this->setRateMinus(0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Forum topic reply: %s', $this->id);
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
     * Set topic
     * 
     * @param ForumTopic $topic
     * @return ForumReply
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     * 
     * @return ForumTopic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return ForumReply
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
     * Set replyOnComment
     * 
     * @param ForumReply $replyOnComment
     * @return ForumReply
     */
    public function setReplyOnComment($replyOnComment = null)
    {
        $this->replyOnComment = $replyOnComment;

        return $this;
    }

    /**
     * Get replyOnComment
     *
     * @return ForumReply 
     */
    public function getReplyOnComment()
    {
        return $this->replyOnComment;
    }

    /**
     * Get reply comments
     * 
     * @return array
     */
    public function getReplyComments()
    {
        return $this->replyComments;
    }

    /**
     * Add reply comment
     * 
     * @param ForumReply $replyComment
     * @return ForumReply
     */
    public function addReplyComment(ForumReply $replyComment)
    {
        if (!$this->replyComments->contains($replyComment)) {
            $this->replyComments->add($replyComment);
            $replyComment->setReplyOnComment($this);
        }

        return $this;
    }

    /**
     * Check if comment has reply comments
     * 
     * @return bool
     */
    public function hasReplyComments()
    {
        return (count($this->replyComments) > 0);
    }

    public function getFormattedDate()
    {
        return $this->getCreatedAt()->format('d.m.Y H:i');
    }

    public function getFormattedRating()
    {
        $rating = $this->getRateAvg();
        $css    = '';
        $prefix = '';

        if ($this->isPositiveRateAvg()) {
            $prefix = '+';
        } elseif ($this->isNegativeRateAvg()) {
            $css = ' negative';
        } else {
            if ($this->isPositiveRateAvg() == 0) {
                return '';
            }
        }

        return sprintf('<span class="score%s">%s%s</span>', $css, $prefix, $rating);
    }

    public function getEnabled()
    {
        return $this->blocked !== true ? 1 : 0;
    }

}
