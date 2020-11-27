<?php

namespace Polls\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;
use Polls\Entity\Poll;
use Polls\Entity\PollOption;

/**
 * @ORM\Entity(repositoryClass="Polls\Repository\PollVoteRepository")
 * @ORM\Table(name="polls_votes")
 * @ORM\HasLifecycleCallbacks
 */
class PollVote 
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
     * @var Poll
     * @ORM\ManyToOne(targetEntity="Polls\Entity\Poll")
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $poll;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="Polls\Entity\PollOption")
     * @ORM\JoinColumn(name="poll_option_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $pollOption;

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
        return sprintf('Poll vote: %s', $this->id);
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
     * Set poll
     * 
     * @param Poll $poll
     * @return PollVote
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
     * Unset poll
     * 
     * @return PollVote
     */
    public function unsetPoll()
    {
        $this->poll = null;

        return $this;
    }

    /**
     * Set pollOption
     * 
     * @param PollOption $pollOption
     * @return PollVote
     */
    public function setPollOption($pollOption)
    {
        $this->pollOption = $pollOption;

        return $this;
    }

    /**
     * Get pollOption
     * 
     * @return PollOption
     */
    public function getPollOption()
    {
        return $this->pollOption;
    }

    /**
     * Unset pollOption
     * 
     * @return PollVote
     */
    public function unsetPollOption()
    {
        $this->pollOption = null;

        return $this;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return PollVote
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
     * Unset member
     * 
     * @return PollVote
     */
    public function unsetMember()
    {
        $this->member = null;

        return $this;
    }

}
