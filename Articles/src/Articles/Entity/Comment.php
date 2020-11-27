<?php

namespace Articles\Entity;

use Articles\Entity\Article;
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
use Members\Entity\Member;
use Polls\Entity\Poll;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\CommentRepository")
 * @ORM\Table(
 *      name="comments",
 *      indexes={
 *          @ORM\Index(name="comments_blocked_idx", columns={"blocked"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Comment 
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
     * @var Article
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $article;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="Polls\Entity\Poll")
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $poll;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Comment", inversedBy="replyComments", cascade={"persist"})
     * @ORM\JoinColumn(name="reply_on_comment_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $replyOnComment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Comment", mappedBy="replyOnComment")
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
        return sprintf('Commment: %s', $this->id);
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
     * Set article
     * 
     * @param Article $article
     * @return Comment
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     * 
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set poll
     * 
     * @param Poll $poll
     * @return Comment
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll
     * 
     * @return Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return Comment
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
     * @param Comment $replyOnComment
     * @return Comment
     */
    public function setReplyOnComment($replyOnComment = null)
    {
        $this->replyOnComment = $replyOnComment;

        return $this;
    }

    /**
     * Get replyOnComment
     *
     * @return Comment 
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
     * @param Comment $replyComment
     * @return Comment
     */
    public function addReplyComment(Comment $replyComment)
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
