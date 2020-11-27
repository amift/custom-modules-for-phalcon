<?php

namespace Polls\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ContentEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Polls\Entity\PollOption;
use Polls\Tool\State;
use Polls\Traits\PollCategoriesEntityTrait;
use Polls\Traits\PollLogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Polls\Repository\PollRepository")
 * @ORM\Table(
 *      name="polls",
 *      indexes={
 *          @ORM\Index(name="polls_state_idx", columns={"state"}),
 *          @ORM\Index(name="polls_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="polls_startpage_idx", columns={"startpage"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Poll 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use ContentEntityTrait;
    use PollCategoriesEntityTrait;
    use PollLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = State::STATE_PENDING;

    /**
     * @var boolean
     * @ORM\Column(name="`startpage`", type="boolean", nullable=false, options={"default":false})
     */
    private $startpage = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="publication_date", type="datetime", nullable=true, options={"default":null})
     */
    private $publicationDate;

    /**
     * @var integer
     * @ORM\Column(name="comments_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $commentsCount;

    /**
     * @var integer
     * @ORM\Column(name="votes_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $votesCount;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Polls\Entity\PollOption", mappedBy="poll", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    private $options;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(State::STATE_PENDING);
        $this->setStartpage(false);
        $this->setPublicationDate(new \DateTime('now'));
        $this->setCommentsCount(0);
        $this->setVotesCount(0);
        $this->options = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->title);
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
     * Set status
     *
     * @param smallint $state
     * @return Poll
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

    /**
     * Set startpage
     * 
     * @param boolean $startpage
     * @return Poll
     */
    public function setStartpage($startpage)
    {
        $this->startpage = $startpage;

        return $this;
    }

    /**
     * Get startpage
     * 
     * @return boolean 
     */
    public function getStartpage()
    {
        return $this->startpage ? 1 : 0;
    }

    /**
     * Check if is startpage
     * 
     * @return boolean 
     */
    public function isStartpage()
    {
        return $this->startpage === true ? true : false;
    }

    /**
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     * @return Poll
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set commentsCount
     * 
     * @param integer $commentsCount
     * @return Poll
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;

        return $this;
    }

    /**
     * Get commentsCount
     * 
     * @return integer 
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * Set votesCount
     * 
     * @param integer $votesCount
     * @return Poll
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
     * Set options
     * 
     * @param $options
     * @return Member
     */
    public function setOptions($options) 
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get $options
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions() 
    {
        return $this->options;
    }

    /**
     * Add option
     * 
     * @param \Polls\Entity\PollOption $option
     */
    public function addOption(PollOption $option)
    {
        if (! $this->options->contains($option)) {
            $this->options->add($option);
            $option->setPoll($this);
        }
        return $this;
    }

    public function removeOption(PollOption $option)
    {
        if ($this->options->contains($option)) {
            $option->unsetPoll();
            $this->options->removeElement($option);
        }
        return $this;
    }

    public function getFormattedOptionsForForm()
    {
        $data = [];

        foreach ($this->options as $option) {
            $data[] = [
                'id' => $option->getId(),
                'title' => $option->getTitle(),
                'ordering' => $option->getOrdering(),
                'imagePublicPath' => $option->getImagePublicPath()
            ];
        }

        return $data;
    }

}
