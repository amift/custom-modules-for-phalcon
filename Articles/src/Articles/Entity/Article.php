<?php

namespace Articles\Entity;

use Articles\Entity\ArticlePoints;
use Articles\Tool\State;
use Articles\Tool\Type;
use Articles\Traits\ArticleCategoriesEntityTrait;
use Articles\Traits\LogicAwareTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\CreatedFromIpEntityTrait;
use Common\Traits\ContentEntityTrait;
//use Common\Traits\MetaDataEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\RateTotalEntityTrait;
use Common\Traits\SlugEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Articles\Repository\ArticleRepository")
 * @ORM\Table(
 *      name="articles",
 *      indexes={
 *          @ORM\Index(name="articles_state_idx", columns={"state"}),
 *          @ORM\Index(name="articles_type_idx", columns={"type"}),
 *          @ORM\Index(name="articles_slug_idx", columns={"slug"}),
 *          @ORM\Index(name="articles_promo_idx", columns={"promo"}),
 *          @ORM\Index(name="articles_actual_idx", columns={"actual"}),
 *          @ORM\Index(name="articles_startpage_idx", columns={"startpage"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Article 
{

    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use SlugEntityTrait;
    use TitleEntityTrait;
    use ContentEntityTrait;
    //use MetaDataEntityTrait;
    use ArticleCategoriesEntityTrait;
    use CreatedFromIpEntityTrait;
    use LogicAwareTrait;
    use RateTotalEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = State::STATE_NEW;

    /**
     * @var smallint
     * @ORM\Column(name="`type`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $type = Type::TYPE_NEWS;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member", inversedBy="articles")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var string
     * @ORM\Column(name="source_url", type="string", nullable=false)
     */
    private $sourceUrl;

    /**
     * @var string
     * @ORM\Column(name="`summary`", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var string
     * @ORM\Column(name="`image`", type="string", nullable=true)
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(name="`image_path`", type="string", nullable=true)
     */
    private $imagePath;

    /**
     * @var string
     * @ORM\Column(name="`video`", type="string", nullable=true)
     */
    private $video;

    /**
     * @var string
     * @ORM\Column(name="media_source_url", type="string", nullable=false)
     */
    private $mediaSourceUrl;

    /**
     * @var string
     * @ORM\Column(name="media_source_name", type="string", length=50, nullable=false)
     */
    private $mediaSourceName;

    /**
     * @var boolean
     * @ORM\Column(name="`promo`", type="boolean", nullable=false, options={"default":false})
     */
    private $promo = false;

    /**
     * @var boolean
     * @ORM\Column(name="`actual`", type="boolean", nullable=false, options={"default":false})
     */
    private $actual = false;

    /**
     * @var boolean
     * @ORM\Column(name="`startpage`", type="boolean", nullable=false, options={"default":false})
     */
    private $startpage = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="publication_date", type="datetime", nullable=true, options={"default":null})
     */
    private $publicationDate;

    /**
     * @var integer
     * @ORM\Column(name="comments_count", type="integer", nullable=false, options={"unsigned":true,"default":0})
     */
    private $commentsCount;

    /**
     * @var ArticlePoints
     * @ORM\OneToOne(targetEntity="Articles\Entity\ArticlePoints", mappedBy="article", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $points;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setState(State::STATE_NEW);
        $this->setType(Type::TYPE_NEWS);
        $this->setPromo(false);
        $this->setActual(false);
        $this->setStartpage(false);
        $this->setPublicationDate(new \DateTime('now'));
        $this->setMediaSourceUrl('');
        $this->setRateAvg(0);
        $this->setRatePlus(0);
        $this->setRateMinus(0);
        $this->setCommentsCount(0);
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
     * Set status
     *
     * @param smallint $state
     * @return Article
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get status
     *
     * @return smallint
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set type
     *
     * @param smallint $type
     * @return Article
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return smallint
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set member
     * 
     * @param Member $member
     * @return Article
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

    /**
     * Set sourceUrl
     *
     * @param string $sourceUrl
     * @return Article
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * Get sourceUrl
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Article
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Article
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     * @return Article
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set video
     *
     * @param string $video
     * @return Article
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set mediaSourceUrl
     *
     * @param string $mediaSourceUrl
     * @return Article
     */
    public function setMediaSourceUrl($mediaSourceUrl)
    {
        $this->mediaSourceUrl = $mediaSourceUrl;

        return $this;
    }

    /**
     * Get mediaSourceUrl
     *
     * @return string
     */
    public function getMediaSourceUrl()
    {
        return $this->mediaSourceUrl;
    }

    /**
     * Set mediaSourceName
     *
     * @param string $mediaSourceName
     * @return Article
     */
    public function setMediaSourceName($mediaSourceName)
    {
        $this->mediaSourceName = $mediaSourceName;

        return $this;
    }

    /**
     * Get mediaSourceName
     *
     * @return string
     */
    public function getMediaSourceName()
    {
        return $this->mediaSourceName;
    }

    /**
     * Set promo
     * 
     * @param boolean $promo
     * @return Article
     */
    public function setPromo($promo)
    {
        $this->promo = $promo;

        return $this;
    }

    /**
     * Get promo
     * 
     * @return boolean 
     */
    public function getPromo()
    {
        return $this->promo ? 1 : 0;
    }

    /**
     * Check if is promo
     * 
     * @return boolean 
     */
    public function isPromo()
    {
        return $this->promo === true ? true : false;
    }

    /**
     * Set actual
     * 
     * @param boolean $actual
     * @return Article
     */
    public function setActual($actual)
    {
        $this->actual = $actual;

        return $this;
    }

    /**
     * Get actual
     * 
     * @return boolean 
     */
    public function getActual()
    {
        return $this->actual ? 1 : 0;
    }

    /**
     * Check if is actual
     * 
     * @return boolean 
     */
    public function isActual()
    {
        return $this->actual === true ? true : false;
    }

    /**
     * Set startpage
     * 
     * @param boolean $startpage
     * @return Article
     */
    public function setStartpage($startpage)
    {
        $this->startpage = $startpage;

        return $this;
    }

    /**
     * Get startpage
     * 
     * @return boolean 
     */
    public function getStartpage()
    {
        return $this->startpage ? 1 : 0;
    }

    /**
     * Check if is startpage
     * 
     * @return boolean 
     */
    public function isStartpage()
    {
        return $this->startpage === true ? true : false;
    }

    /**
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     * @return Article
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set commentsCount
     * 
     * @param integer $commentsCount
     * @return Article
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
     * Set points
     * 
     * @param ArticlePoints $points
     * @return Article
     */
    public function setPoints(ArticlePoints $points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     * 
     * @return ArticlePoints
     */
    public function getPoints()
    {
        return $this->points;
    }

}
