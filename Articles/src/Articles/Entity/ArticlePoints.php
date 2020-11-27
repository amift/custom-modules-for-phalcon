<?php

namespace Articles\Entity;

use Articles\Entity\Article;
use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="articles_points")
 * @ORM\HasLifecycleCallbacks
 */
class ArticlePoints 
{

    use ObjectSimpleHydrating;

    /**
     * @var Article
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Articles\Entity\Article", inversedBy="points")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $article;

    /**
     * @var integer
     * @ORM\Column(name="publication_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $publicationPts;

    /**
     * @var \DateTime
     * @ORM\Column(name="publication_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $publicationPtsAt;

    /**
     * @var integer
     * @ORM\Column(name="promo_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $promoPts;

    /**
     * @var \DateTime
     * @ORM\Column(name="promo_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $promoPtsAt;

    /**
     * @var integer
     * @ORM\Column(name="startpage_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $startpagePts;

    /**
     * @var \DateTime
     * @ORM\Column(name="startpage_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $startpagePtsAt;

    /**
     * @var integer
     * @ORM\Column(name="rating_1_lvl_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $rating1LvlPts;

    /**
     * @var \DateTime
     * @ORM\Column(name="rating_1_lvl_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $rating1LvlPtsAt;

    /**
     * @var integer
     * @ORM\Column(name="rating_2_lvl_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $rating2LvlPts;

    /**
     * @var \DateTime
     * @ORM\Column(name="rating_2_lvl_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $rating2LvlPtsAt;

    /**
     * @var integer
     * @ORM\Column(name="rating_3_lvl_pts", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $rating3LvlPts;

    /**
     * @var \DateTime
     * @ORM\Column(name="rating_3_lvl_pts_at", type="datetime", nullable=true, options={"default":null})
     */
    private $rating3LvlPtsAt;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setPublicationPts(0);
        $this->setPromoPts(0);
        $this->setStartpagePts(0);
        $this->setRating1LvlPts(0);
        $this->setRating2LvlPts(0);
        $this->setRating3LvlPts(0);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Article points: %s', $this->article->getId());
    }

    /**
     * Set article
     * 
     * @param Article $article
     * @return ArticlePoints
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
     * Set publicationPts 
     *
     * @param int $publicationPts
     * @return ArticlePoints
     */
    public function setPublicationPts($publicationPts)
    {
        $this->publicationPts = $publicationPts;

        return $this;
    }

    /**
     * Get publicationPts
     *
     * @return int
     */
    public function getPublicationPts()
    {
        return $this->publicationPts;
    }

    /**
     * Set publicationPtsAt
     *
     * @param \DateTime $publicationPtsAt
     * @return Article
     */
    public function setPublicationPtsAt($publicationPtsAt)
    {
        $this->publicationPtsAt = $publicationPtsAt;

        return $this;
    }

    /**
     * Get publicationPtsAt
     *
     * @return \DateTime
     */
    public function getPublicationPtsAt()
    {
        return $this->publicationPtsAt;
    }

    /**
     * Set promoPts 
     *
     * @param int $promoPts
     * @return ArticlePoints
     */
    public function setPromoPts($promoPts)
    {
        $this->promoPts = $promoPts;

        return $this;
    }

    /**
     * Get promoPts
     *
     * @return int
     */
    public function getPromoPts()
    {
        return $this->promoPts;
    }

    /**
     * Set promoPtsAt
     *
     * @param \DateTime $promoPtsAt
     * @return Article
     */
    public function setPromoPtsAt($promoPtsAt)
    {
        $this->promoPtsAt = $promoPtsAt;

        return $this;
    }

    /**
     * Get promoPtsAt
     *
     * @return \DateTime
     */
    public function getPromoPtsAt()
    {
        return $this->promoPtsAt;
    }

    /**
     * Set startpagePts 
     *
     * @param int $startpagePts
     * @return ArticlePoints
     */
    public function setStartpagePts($startpagePts)
    {
        $this->startpagePts = $startpagePts;

        return $this;
    }

    /**
     * Get startpagePts
     *
     * @return int
     */
    public function getStartpagePts()
    {
        return $this->startpagePts;
    }

    /**
     * Set startpagePtsAt
     *
     * @param \DateTime $startpagePtsAt
     * @return Article
     */
    public function setStartpagePtsAt($startpagePtsAt)
    {
        $this->startpagePtsAt = $startpagePtsAt;

        return $this;
    }

    /**
     * Get startpagePtsAt
     *
     * @return \DateTime
     */
    public function getStartpagePtsAt()
    {
        return $this->startpagePtsAt;
    }

    /**
     * Set rating1LvlPts 
     *
     * @param int $rating1LvlPts
     * @return ArticlePoints
     */
    public function setRating1LvlPts($rating1LvlPts)
    {
        $this->rating1LvlPts = $rating1LvlPts;

        return $this;
    }

    /**
     * Get rating1LvlPts
     *
     * @return int
     */
    public function getRating1LvlPts()
    {
        return $this->rating1LvlPts;
    }

    /**
     * Set rating1LvlPtsAt
     *
     * @param \DateTime $rating1LvlPtsAt
     * @return Article
     */
    public function setRating1LvlPtsAt($rating1LvlPtsAt)
    {
        $this->rating1LvlPtsAt = $rating1LvlPtsAt;

        return $this;
    }

    /**
     * Get rating1LvlPtsAt
     *
     * @return \DateTime
     */
    public function getRating1LvlPtsAt()
    {
        return $this->rating1LvlPtsAt;
    }

    /**
     * Set rating2LvlPts 
     *
     * @param int $rating2LvlPts
     * @return ArticlePoints
     */
    public function setRating2LvlPts($rating2LvlPts)
    {
        $this->rating2LvlPts = $rating2LvlPts;

        return $this;
    }

    /**
     * Get rating2LvlPts
     *
     * @return int
     */
    public function getRating2LvlPts()
    {
        return $this->rating2LvlPts;
    }

    /**
     * Set rating2LvlPtsAt
     *
     * @param \DateTime $rating2LvlPtsAt
     * @return Article
     */
    public function setRating2LvlPtsAt($rating2LvlPtsAt)
    {
        $this->rating2LvlPtsAt = $rating2LvlPtsAt;

        return $this;
    }

    /**
     * Get rating2LvlPtsAt
     *
     * @return \DateTime
     */
    public function getRating2LvlPtsAt()
    {
        return $this->rating2LvlPtsAt;
    }

    /**
     * Set rating3LvlPts 
     *
     * @param int $rating3LvlPts
     * @return ArticlePoints
     */
    public function setRating3LvlPts($rating3LvlPts)
    {
        $this->rating3LvlPts = $rating3LvlPts;

        return $this;
    }

    /**
     * Get rating3LvlPts
     *
     * @return int
     */
    public function getRating3LvlPts()
    {
        return $this->rating3LvlPts;
    }

    /**
     * Set rating3LvlPtsAt
     *
     * @param \DateTime $rating3LvlPtsAt
     * @return Article
     */
    public function setRating3LvlPtsAt($rating3LvlPtsAt)
    {
        $this->rating3LvlPtsAt = $rating3LvlPtsAt;

        return $this;
    }

    /**
     * Get rating3LvlPtsAt
     *
     * @return \DateTime
     */
    public function getRating3LvlPtsAt()
    {
        return $this->rating3LvlPtsAt;
    }

    public function getPtsTotalValue()
    {
        $total = 0;
        $total = $total + $this->publicationPts;
        $total = $total + $this->promoPts;
        $total = $total + $this->startpagePts;
        $total = $total + $this->rating1LvlPts;
        $total = $total + $this->rating2LvlPts;
        $total = $total + $this->rating3LvlPts;

        return $total;
    }

}
