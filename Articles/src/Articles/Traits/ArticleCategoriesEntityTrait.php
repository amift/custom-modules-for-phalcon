<?php

namespace Articles\Traits;

use Articles\Entity\Category;

trait ArticleCategoriesEntityTrait
{

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category", inversedBy="articlesLvl1")
     * @ORM\JoinColumn(name="category_id_lvl1", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl1;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category", inversedBy="articlesLvl2")
     * @ORM\JoinColumn(name="category_id_lvl2", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl2;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Category", inversedBy="articlesLvl3")
     * @ORM\JoinColumn(name="category_id_lvl3", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $categoryLvl3;

    /**
     * Set categoryLvl1
     * 
     * @param Category $categoryLvl1
     * @return Article
     */
    public function setCategoryLvl1($categoryLvl1)
    {
        $this->categoryLvl1 = $categoryLvl1;

        return $this;
    }

    /**
     * Get categoryLvl1
     * 
     * @return Category
     */
    public function getCategoryLvl1()
    {
        return $this->categoryLvl1;
    }

    /**
     * Set categoryLvl2
     * 
     * @param Category $categoryLvl2
     * @return Article
     */
    public function setCategoryLvl2($categoryLvl2)
    {
        $this->categoryLvl2 = $categoryLvl2;

        return $this;
    }

    /**
     * Get categoryLvl2
     * 
     * @return Category
     */
    public function getCategoryLvl2()
    {
        return $this->categoryLvl2;
    }

    /**
     * Set categoryLvl3
     * 
     * @param Category $categoryLvl3
     * @return Article
     */
    public function setCategoryLvl3($categoryLvl3)
    {
        $this->categoryLvl3 = $categoryLvl3;

        return $this;
    }

    /**
     * Get categoryLvl3
     * 
     * @return Category
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
