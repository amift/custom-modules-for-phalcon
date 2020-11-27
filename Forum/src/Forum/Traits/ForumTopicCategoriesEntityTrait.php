<?php

namespace Forum\Traits;

use Forum\Entity\ForumCategory;

trait ForumTopicCategoriesEntityTrait
{

    /**
     * @var ForumCategory
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumCategory")
     * @ORM\JoinColumn(name="category_id_lvl1", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl1;

    /**
     * @var ForumCategory
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumCategory")
     * @ORM\JoinColumn(name="category_id_lvl2", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl2;

    /**
     * @var ForumCategory
     * @ORM\ManyToOne(targetEntity="Forum\Entity\ForumCategory")
     * @ORM\JoinColumn(name="category_id_lvl3", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl3;

    /**
     * Set categoryLvl1
     * 
     * @param ForumCategory $categoryLvl1
     * @return ForumTopic
     */
    public function setCategoryLvl1($categoryLvl1)
    {
        $this->categoryLvl1 = $categoryLvl1;

        return $this;
    }

    /**
     * Get categoryLvl1
     * 
     * @return ForumCategory
     */
    public function getCategoryLvl1()
    {
        return $this->categoryLvl1;
    }

    /**
     * Set categoryLvl2
     * 
     * @param ForumCategory $categoryLvl2
     * @return ForumTopic
     */
    public function setCategoryLvl2($categoryLvl2)
    {
        $this->categoryLvl2 = $categoryLvl2;

        return $this;
    }

    /**
     * Get categoryLvl2
     * 
     * @return ForumCategory
     */
    public function getCategoryLvl2()
    {
        return $this->categoryLvl2;
    }

    /**
     * Set categoryLvl3
     * 
     * @param ForumCategory $categoryLvl3
     * @return ForumTopic
     */
    public function setCategoryLvl3($categoryLvl3)
    {
        $this->categoryLvl3 = $categoryLvl3;

        return $this;
    }

    /**
     * Get categoryLvl3
     * 
     * @return ForumCategory
     */
    public function getCategoryLvl3()
    {
        return $this->categoryLvl3;
    }

    /**
     * @return array
     */
    public function getCategoriesIdAndTitleAsArray()
    {
        $categories = [];

        $c1 = $this->getCategoryLvl1();
        if (is_object($c1)) {
            $categories[$c1->getId()] = $c1->getTitle();
        }

        $c2 = $this->getCategoryLvl2();
        if (is_object($c2)) {
            $categories[$c2->getId()] = $c2->getTitle();
        }

        $c3 = $this->getCategoryLvl3();
        if (is_object($c3)) {
            $categories[$c3->getId()] = $c3->getTitle();
        }

        return $categories;
    }

}
