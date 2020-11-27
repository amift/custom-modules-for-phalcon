<?php

namespace Articles\Entity;

use Articles\Entity\Comment;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateOneEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\CommentRateRepository")
 * @ORM\Table(name="comments_rates")
 * @ORM\HasLifecycleCallbacks
 */
class CommentRate 
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
        return sprintf('Commment rate: %s', $this->id);
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
     * @return CommentRate
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
     * @return CommentRate
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
