<?php

namespace Forum\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\EnabledEntityTrait;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\ContentEntityTrait;
use Common\Traits\LevelEntityTrait;
use Common\Traits\OrderingEntityTrait;
use Common\Traits\MetaDataEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Forum\Entity\ForumTopic;
use Forum\Traits\ForumLastReplyEntityTrait;
use Forum\Traits\ForumCategoryLogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Forum\Repository\ForumCategoryRepository")
 * @ORM\Table(
 *      name="forum_categories",
 *      indexes={
 *          @ORM\Index(name="forum_categories_enabled_idx", columns={"enabled"}),
 *          @ORM\Index(name="forum_categories_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="forum_categories_lvl_idx", columns={"lvl"}),
 *          @ORM\Index(name="forum_categories_ordering_idx", columns={"ordering"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class ForumCategory 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use EnabledEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use ContentEntityTrait;
    use LevelEntityTrait;
    use OrderingEntityTrait;
    use MetaDataEntityTrait;
    use ForumLastReplyEntityTrait;
    use ForumCategoryLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumCategory", inversedBy="childrens", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Forum\Entity\ForumCategory", mappedBy="parent")
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    private $childrens;

    /**
     * @var integer
     * @ORM\Column(name="topics_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $topicsCount;

    /**
     * @var integer
     * @ORM\Column(name="comments_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $commentsCount;

    /**
     * @var integer
     * @ORM\Column(name="views_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $viewsCount;

    /**
     * @var ForumTopic
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumTopic")
     * @ORM\JoinColumn(name="last_reply_on_topic", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $lastReplyOnTopic;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setEnabled(true);
        $this->setLevel(1);
        $this->setTopicsCount(0);
        $this->setCommentsCount(0);
        $this->setViewsCount(0);
        $this->childrens = new ArrayCollection();
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
     * Set parent
     * 
     * @param ForumCategory $parent
     * @return ForumCategory
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return ForumCategory 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get childrens
     * 
     * @return array
     */
    public function getChildrens()
    {
        return $this->childrens;
    }

    /**
     * Add children
     * 
     * @param ForumCategory $children
     * @return ForumCategory
     */
    public function addChildren(ForumCategory $children)
    {
        if (!$this->childrens->contains($children)) {
            $this->childrens->add($children);
            $children->setParent($this);
        }

        return $this;
    }

    /**
     * Check if category has childrens
     * 
     * @return bool
     */
    public function hasChildrens()
    {
        return (count($this->childrens) > 0);
    }

    /**
     * Set topicsCount
     * 
     * @param integer $topicsCount
     * @return ForumCategory
     */
    public function setTopicsCount($topicsCount)
    {
        $this->topicsCount = $topicsCount;

        return $this;
    }

    /**
     * Get topicsCount
     * 
     * @return integer 
     */
    public function getTopicsCount()
    {
        return $this->topicsCount;
    }

    /**
     * Set commentsCount
     * 
     * @param integer $commentsCount
     * @return ForumCategory
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
     * Set viewsCount
     * 
     * @param integer $viewsCount
     * @return ForumCategory
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;

        return $this;
    }

    /**
     * Get viewsCount
     * 
     * @return integer 
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    /**
     * Set lastReplyOnTopic
     * 
     * @param ForumTopic $lastReplyOnTopic
     * @return ForumCategory
     */
    public function setLastReplyOnTopic($lastReplyOnTopic)
    {
        $this->lastReplyOnTopic = $lastReplyOnTopic;

        return $this;
    }

    /**
     * Get lastReplyOnTopic
     * 
     * @return ForumTopic
     */
    public function getLastReplyOnTopic()
    {
        return $this->lastReplyOnTopic;
    }

}
