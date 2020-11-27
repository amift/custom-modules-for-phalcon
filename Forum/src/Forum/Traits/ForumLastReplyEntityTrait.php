<?php

namespace Forum\Traits;

use Members\Entity\Member;

trait ForumLastReplyEntityTrait
{

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="last_reply_by", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $lastReplyBy;

    /**
     * @var string
     * @ORM\Column(name="last_reply_by_username", type="string", length=75, nullable=true)
     */
    private $lastReplyByUsername = '';

    /**
     * @var \DateTime
     * @ORM\Column(name="last_reply_date", type="datetime", nullable=true)
     */
    private $lastReplyAt = null;

    /**
     * @var string
     * @ORM\Column(name="last_reply", type="text", nullable=true)
     */
    private $lastReply;

    /**
     * Set lastReplyBy
     * 
     * @param Member $lastReplyBy
     * @return $this
     */
    public function setLastReplyBy($lastReplyBy)
    {
        $this->lastReplyBy = $lastReplyBy;

        return $this;
    }

    /**
     * Get lastReplyBy
     * 
     * @return Member
     */
    public function getLastReplyBy()
    {
        return $this->lastReplyBy;
    }

    /**
     * Set lastReplyByUsername
     *
     * @param string $lastReplyByUsername
     * @return $this
     */
    public function setLastReplyByUsername($lastReplyByUsername)
    {
        $this->lastReplyByUsername = $lastReplyByUsername;

        return $this;
    }

    /**
     * Get lastReplyByUsername
     *
     * @return string
     */
    public function getLastReplyByUsername()
    {
        return $this->lastReplyByUsername;
    }

    /**
     * Set lastReplyAt
     *
     * @param \DateTime $lastReplyAt
     * @return $this
     */
    public function setLastReplyAt($lastReplyAt)
    {
        $this->lastReplyAt = $lastReplyAt;

        return $this;
    }

    /**
     * Get lastReplyAt
     *
     * @return \DateTime
     */
    public function getLastReplyAt()
    {
        return $this->lastReplyAt;
    }

    /**
     * Set lastReply
     *
     * @param string $lastReply
     * @return $this
     */
    public function setLastReply($lastReply)
    {
        $this->lastReply = $lastReply;

        return $this;
    }

    /**
     * Get lastReply
     *
     * @return string
     */
    public function getLastReply()
    {
        return $this->lastReply;
    }

}
