<?php

namespace Documents\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\EnabledEntityTrait;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\ContentEntityTrait;
use Common\Traits\MetaDataEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Documents\Repository\DocumentRepository")
 * @ORM\Table(
 *      name="documents",
 *      indexes={
 *          @ORM\Index(name="documents_created_at_idx", columns={"created_at"}),
 *          @ORM\Index(name="documents_enabled_idx", columns={"enabled"}),
 *          @ORM\Index(name="documents_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="documents_title_idx", columns={"title"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="documents_key_unique_idx", columns={"key"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Document
{

    use ObjectSimpleHydrating;
    use EnabledEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use ContentEntityTrait;
    use MetaDataEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="`key`", type="string", nullable=false)
     */
    private $key;

    /**
     * @var Document
     * @ORM\ManyToOne(targetEntity="Documents\Entity\Document", inversedBy="childrens", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Documents\Entity\Document", mappedBy="parent")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $childrens;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->childrens = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return sprintf('Document: %s', $this->title);
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
     * Set key
     *
     * @param string $key
     * @return Document
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set parent
     * 
     * @param Document $parent
     * @return Document
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Document 
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
     * @param Document $children
     * @return Document
     */
    public function addChildren(Document $children)
    {
        if (!$this->childrens->contains($children)) {
            $this->childrens->add($children);
            $children->setParent($this);
        }

        return $this;
    }

    /**
     * Check if document has childrens
     * 
     * @return bool
     */
    public function hasChildrens()
    {
        return (count($this->childrens) > 0);
    }

}