<?php

namespace Forum\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Forum\Entity\ForumReply;
use Forum\Tool\ForumReportedReplyState;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumReportedReplyRepository")
 * @ORM\Table(
 *      name="forum_replies_reported",
 *      indexes={
 *          @ORM\Index(name="forum_replies_reported_state_idx", columns={"state"}),
 *          @ORM\Index(name="forum_replies_reported_reason_idx", columns={"reason"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ForumReportedReply 
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
     * @var ForumReply
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumReply")
     * @ORM\JoinColumn(name="reply_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $reply;

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
    private $state = ForumReportedReplyState::STATE_NEW;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(ForumReportedReplyState::STATE_NEW);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Forum topic reported reply: %s', $this->id);
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
     * Set reply
     * 
     * @param ForumReply $reply
     * @return ForumReportedReply
     */
    public function setReply($reply)
    {
        $this->reply = $reply;

        return $this;
    }

    /**
     * Get reply
     * 
     * @return ForumReply
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return ForumReportedReply
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
     * @return ForumReportedReply
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
     * @return ForumReportedReply
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
     * @return ForumReportedReply
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
        return (int)$this->state === ForumReportedReplyState::STATE_NEW ? true : false;
    }

    public function isStateAccepted()
    {
        return (int)$this->state === ForumReportedReplyState::STATE_ACCEPTED ? true : false;
    }

    public function isStateIgnored()
    {
        return (int)$this->state === ForumReportedReplyState::STATE_IGNORED ? true : false;
    }

}
