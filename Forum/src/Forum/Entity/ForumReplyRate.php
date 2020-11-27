<?php

namespace Forum\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateOneEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Forum\Entity\ForumReply;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumReplyRateRepository")
 * @ORM\Table(name="forum_replies_rates")
 * @ORM\HasLifecycleCallbacks
 */
class ForumReplyRate 
{

    use ObjectSimpleHydrating;
    use RateOneEntityTrait;
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
     * Class constructor
     */
    public function __construct()
    {
        //
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Forum topic reply rate: %s', $this->id);
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
     * @return ForumReplyRate
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
     * @return ForumReplyRate
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

}
