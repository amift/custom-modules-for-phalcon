<?php

namespace Articles\Traits;

use Articles\Entity\Article;

trait CategoryArticlesEntityTrait
{

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Article", mappedBy="categoryLvl1", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $articlesLvl1;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Article", mappedBy="categoryLvl2", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $articlesLvl2;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Articles\Entity\Article", mappedBy="categoryLvl3", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $articlesLvl3;

    /**
     * Set articlesLvl1
     * 
     * @param $articlesLvl1
     * @return Category
     */
    public function setArticlesLvl1($articlesLvl1) 
    {
        $this->articlesLvl1 = $articlesLvl1;

        return $this;
    }

    /**
     * Get articlesLvl1
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticlesLvL1() 
    {
        return $this->articlesLvl1;
    }

    /**
     * Add Article
     * 
     * @param \Articles\Entity\Article $article
     */
    public function addArticleLvl1(Article $article)
    {
        if (! $this->articlesLvl1->contains($article)) {
            $this->articlesLvl1->add($article);
            $article->setCategoryLvl1($this);
        }
    }

    /**
     * Set articlesLvl2
     * 
     * @param $articlesLvl2
     * @return Category
     */
    public function setArticlesLvl2($articlesLvl2) 
    {
        $this->articlesLvl2 = $articlesLvl2;

        return $this;
    }

    /**
     * Get articlesLvl2
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticlesLvL2() 
    {
        return $this->articlesLvl2;
    }

    /**
     * Add Article
     * 
     * @param \Articles\Entity\Article $article
     */
    public function addArticleLvl2(Article $article)
    {
        if (! $this->articlesLvl2->contains($article)) {
            $this->articlesLvl2->add($article);
            $article->setCategoryLvl2($this);
        }
    }

    /**
     * Set articlesLvl3
     * 
     * @param $articlesLvl3
     * @return Category
     */
    public function setArticlesLvl3($articlesLvl3) 
    {
        $this->articlesLvl3 = $articlesLvl3;

        return $this;
    }

    /**
     * Get articlesLvl3
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticlesLvL3() 
    {
        return $this->articlesLvl3;
    }

    /**
     * Add Article
     * 
     * @param \Articles\Entity\Article $article
     */
    public function addArticleLvl3(Article $article)
    {
        if (! $this->articlesLvl3->contains($article)) {
            $this->articlesLvl3->add($article);
            $article->setCategoryLvl3($this);
        }
    }

}
