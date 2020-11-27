<?php

namespace Articles\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;
use Articles\Entity\Article;
use Articles\Entity\Tag;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\ArticleTagRepository")
 * @ORM\Table(
 *      name="articles_tags",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="articles_tags_unique_idx", columns={"article_id", "tag_id"}),
 *      }
 * )
 */
class ArticleTag 
{

    use ObjectSimpleHydrating;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Article
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $article;

    /**
     * @var Tag
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Tag")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tag;

    /**
     * Class constructor
     */
    public function __construct()
    {
        //
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
     * Set article 
     *
     * @param Article $article
     * @return ArticleTag
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set tag 
     *
     * @param Tag $tag
     * @return ArticleTag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return Article
     */
    public function getTag()
    {
        return $this->tag;
    }

}
