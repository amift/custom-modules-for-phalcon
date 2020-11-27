<?php

namespace Forum\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateOneEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Forum\Entity\ForumTopic;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumTopicRateRepository")
 * @ORM\Table(name="forum_topics_rates")
 * @ORM\HasLifecycleCallbacks
 */
class ForumTopicRate 
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
        return sprintf('Forum topic rate: %s', $this->id);
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
     * @return ForumTopicRate
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
     * @return ForumTopicRate
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
