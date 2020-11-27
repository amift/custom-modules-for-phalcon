<?php

namespace Articles\Entity;

use Articles\Entity\Comment;
use Articles\Tool\ReportState;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\ReportedCommentRepository")
 * @ORM\Table(
 *      name="comments_reported",
 *      indexes={
 *          @ORM\Index(name="comments_reported_state_idx", columns={"state"}),
 *          @ORM\Index(name="comments_reported_reason_idx", columns={"reason"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ReportedComment 
{

    use ObjectSimpleHydrating;
    use IpAddreesEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;
    use CreatedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Comment
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Comment")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $comment;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var string
     * @ORM\Column(name="reason", type="string", nullable=true)
     */
    private $reason;

    /**
     * @var string
     * @ORM\Column(name="`note`", type="text", nullable=true)
     */
    private $note;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = ReportState::STATE_NEW;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(ReportState::STATE_NEW);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Reported commment: %s', $this->id);
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
     * Set comment
     * 
     * @param Comment $comment
     * @return ReportedComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     * 
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return ReportedComment
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
     * Set reason
     *
     * @param string $reason
     * @return ReportedComment
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return ReportedComment
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set status
     *
     * @param smallint $state
     * @return ReportedComment
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

    public function isStateNew()
    {
        return (int)$this->state === ReportState::STATE_NEW ? true : false;
    }

    public function isStateAccepted()
    {
        return (int)$this->state === ReportState::STATE_ACCEPTED ? true : false;
    }

    public function isStateIgnored()
    {
        return (int)$this->state === ReportState::STATE_IGNORED ? true : false;
    }

}
