<?php

namespace Communication\Entity;

use Articles\Entity\Article;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\OrderingEntityTrait;
use Communication\Entity\Newsletter;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Communication\Repository\NewsletterArticleRepository")
 * @ORM\Table(
 *      name="communication_newsletters_articles",
 *      indexes={@ORM\Index(name="cna_ordering_idx", columns={"ordering"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class NewsletterArticle
{
    use ObjectSimpleHydrating;
    use OrderingEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Newsletter
     * @ORM\ManyToOne(targetEntity="Communication\Entity\Newsletter", inversedBy="articles")
     * @ORM\JoinColumn(name="newsletter_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $newsletter;

    /**
     * @var Article
     * @ORM\ManyToOne(targetEntity="Articles\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $article;

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Newsletter article: %s', $this->title);
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
     * Set newsletter
     *
     * @param Newsletter $newsletter
     * @return NewsletterArticle
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * Get newsletter
     *
     * @return Newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Unset newsletter
     *
     * @return NewsletterArticle
     */
    public function unsetNewsletter()
    {
        $this->newsletter = null;
        return $this;
    }

    /**
     * Set article
     *
     * @param Article $article
     * @return NewsletterArticle
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
}