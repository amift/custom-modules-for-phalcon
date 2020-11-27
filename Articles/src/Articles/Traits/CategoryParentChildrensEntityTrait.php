<?php

namespace Articles\Traits;

use Articles\Entity\Category;

trait CategoryParentChildrensEntityTrait
{

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category", inversedBy="childrens", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Category", mappedBy="parent")
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    private $childrens;

    /**
     * Set parent
     * 
     * @param Category $parent
     * @return Category
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Category 
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
     * @param Category $children
     * @return Category
     */
    public function addChildren(Category $children)
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

}
