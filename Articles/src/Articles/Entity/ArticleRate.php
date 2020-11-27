<?php

namespace Articles\Entity;

use Articles\Entity\Article;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateOneEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\ArticleRateRepository")
 * @ORM\Table(name="articles_rates")
 * @ORM\HasLifecycleCallbacks
 */
class ArticleRate 
{

    use ObjectSimpleHydrating;
    use RateOneEntityTrait;
    use IpAddreesEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;
    use CreatedAtEntityTrait;

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
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $article;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * Class constructor
     */
    public function __construct()
    {
        //
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Article rate: %s', $this->id);
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
     * @return ArticleRate
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
     * Set member
     * 
     * @param Member $member
     * @return ArticleRate
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     * 
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

}
