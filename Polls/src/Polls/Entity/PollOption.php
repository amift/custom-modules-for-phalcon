<?php

namespace Polls\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\OrderingEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Polls\Entity\Poll;
use Polls\Traits\PollOptionLogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Polls\Repository\PollOptionRepository")
 * @ORM\Table(
 *      name="polls_options",
 *      indexes={
 *          @ORM\Index(name="polls_ordering_idx", columns={"ordering"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class PollOption 
{

    use ObjectSimpleHydrating;
    use TitleEntityTrait;
    use OrderingEntityTrait;
    use PollOptionLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="Polls\Entity\Poll", inversedBy="options")
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $poll;

    /**
     * @var integer
     * @ORM\Column(name="votes_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $votesCount;

    /**
     * @var integer
     * @ORM\Column(name="votes_percent", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $votesPercent;

    /**
     * @var string
     * @ORM\Column(name="`image`", type="string", nullable=true)
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(name="`image_path`", type="string", nullable=true)
     */
    private $imagePath;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setVotesCount(0);
        $this->setVotesPercent(0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Poll option: %s', $this->title);
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
     * @return PollOption
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
     * @return PollOption
     */
    public function unsetPoll()
    {
        $this->poll = null;

        return $this;
    }

    /**
     * Set votesCount
     * 
     * @param integer $votesCount
     * @return PollOption
     */
    public function setVotesCount($votesCount)
    {
        $this->votesCount = $votesCount;

        return $this;
    }

    /**
     * Get votesCount
     * 
     * @return integer 
     */
    public function getVotesCount()
    {
        return $this->votesCount;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return PollOption
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if image is filled or not
     * 
     * @return bool
     */
    public function hasImage()
    {
        return $this->image !== null && $this->image !== '' ? true : false;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     * @return PollOption
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set votesPercent
     * 
     * @param integer $votesPercent
     * @return PollOption
     */
    public function setVotesPercent($votesPercent)
    {
        $this->votesPercent = $votesPercent;

        return $this;
    }

    /**
     * Get votesPercent
     * 
     * @return integer 
     */
    public function getVotesPercent()
    {
        return $this->votesPercent;
    }

}
