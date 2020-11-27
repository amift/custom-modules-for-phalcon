<?php

namespace Communication\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\TitleEntityTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\MessageEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Communication\Tool\NewsletterState as State;
use Communication\Tool\NewsletterType as Type;

/**
 * @ORM\Entity(repositoryClass="Communication\Repository\NewsletterRepository")
 * @ORM\Table(
 *      name="communication_newsletters",
 *      indexes={
 *          @ORM\Index(name="communication_newsletters_type_idx", columns={"type"}),
 *          @ORM\Index(name="communication_newsletters_state_idx", columns={"state"}),
 *          @ORM\Index(name="communication_newsletters_to_send_at_idx", columns={"to_send_at"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Newsletter
{
    use ObjectSimpleHydrating;
    use TitleEntityTrait;
    use MessageEntityTrait;
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
     * @var smallint
     * @ORM\Column(name="`type`", type="smallint", options={"unsigned":true})
     */
    private $type;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", options={"unsigned":true})
     */
    private $state;
    
    /**
     * @var array
     * @ORM\Column(name="attachments", type="json_array", nullable=true)
     */
    private $attachments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Communication\Entity\NewsletterArticle", mappedBy="newsletter", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    private $articles;

    /**
     * @var \DateTime
     * @ORM\Column(name="to_send_at", type="datetime", nullable=false)
     */
    private $toSendAt;

    /**
     * @var array
     * @ORM\Column(name="receiver_criterias", type="json_array", nullable=true)
     */
    private $receiverCriterias;

    /**
     * @var \DateTime
     * @ORM\Column(name="processing_started_at", type="datetime", nullable=true)
     */
    private $processingStartedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="processing_finished_at", type="datetime", nullable=true)
     */
    private $processingFinishedAt;

    /**
     * @var int
     * @ORM\Column(name="notifications_count", type="integer", nullable=true, options={"unsigned":true,"default":null})
     */
    private $notificationsCount;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setTitle('');
        $this->setFromName('');
        $this->setFromEmail('');
        $this->setSubject('');
        $this->setBody('');
        $this->setType(Type::CUSTOM);
        $this->setState(State::TEMPORARY);
        $this->setArticles(new ArrayCollection());
        $this->setAttachments([]);
        $this->setToSendAt(new \DateTime('now'));
        $this->setReceiverCriterias([]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "[%s] %s", Type::getLabel($this->type), $this->title
        );
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
     * Set type
     *
     * @param smallint $type
     * @return Newsletter
     */
    public function setType($type)
    {
        $this->type = (int)$type;
        return $this;
    }

    /**
     * Get type
     *
     * @return smallint
     */
    public function getType()
    {
        return (int)$this->type;
    }

    /**
     * Is type "custom"
     *
     * @return boolean
     */
    public function isTypeCustom()
    {
        return $this->type === Type::CUSTOM;
    }

    /**
     * Is type "weekly"
     *
     * @return boolean
     */
    public function isTypeWeekly()
    {
        return $this->type === Type::WEEKLY;
    }

    /**
     * Set state
     *
     * @param smallint $state
     * @return Newsletter
     */
    public function setState($state)
    {
        $this->state = (int)$state;
        return $this;
    }

    /**
     * Get state
     *
     * @return smallint
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set attachments
     *
     * @param array $attachments
     * @return Newsletter
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set articles
     *
     * @param $articles
     * @return Newsletter
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
        return $this;
    }

    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add article
     *
     * @param \Communication\Entity\NewsletterArticle $article
     */
    public function addArticle(NewsletterArticle $article)
    {
        if (! $this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setNewsletter($this);
        }
        return $this;
    }

    /**
     * Remove article
     *
     * @param \Communication\Entity\NewsletterArticle $article
     * @return Newsletter
     */
    public function removeArticle(NewsletterArticle $article)
    {
        if ($this->articles->contains($article)) {
            $article->unsetNewsletter();
            $this->articles->removeElement($article);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getFormattedArticlesForForm()
    {
        $data = [];

        foreach ($this->articles as $article) {
            $news = $article->getArticle();
            $data[] = [
                'id' => $article->getId(),
                'article_id' => $news->getId(),
                'title' => $news->getTitle(),
                'ordering' => $article->getOrdering()
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getArtilesIdsAsString()
    {
        $ids = [];

        foreach ($this->articles as $article) {
            $ids[] = $article->getArticle()->getId();
        }

        return implode(',', $ids);
    }

    /**
     * Set toSendAt
     *
     * @param \DateTime $toSendAt
     * @return Newsletter
     */
    public function setToSendAt($toSendAt)
    {
        $this->toSendAt = $toSendAt;
        return $this;
    }

    /**
     * Get toSendAt
     *
     * @return \DateTime 
     */
    public function getToSendAt()
    {
        return $this->toSendAt;
    }

    /**
     * Set receiverCriterias
     *
     * @param array $receiverCriterias
     * @return Newsletter
     */
    public function setReceiverCriterias($receiverCriterias)
    {
        $this->receiverCriterias = $receiverCriterias;
        return $this;
    }

    /**
     * Get receiverCriterias
     *
     * @return array
     */
    public function getReceiverCriterias()
    {
        return $this->receiverCriterias;
    }

    /**
     * Set processingStartedAt
     *
     * @param \DateTime $processingStartedAt
     * @return Newsletter
     */
    public function setProcessingStartedAt($processingStartedAt)
    {
        $this->processingStartedAt = $processingStartedAt;
        return $this;
    }

    /**
     * Get processingStartedAt
     *
     * @return \DateTime 
     */
    public function getProcessingStartedAt()
    {
        return $this->processingStartedAt;
    }

    /**
     * Set processingFinishedAt
     *
     * @param \DateTime $processingFinishedAt
     * @return Newsletter
     */
    public function setProcessingFinishedAt($processingFinishedAt)
    {
        $this->processingFinishedAt = $processingFinishedAt;
        return $this;
    }

    /**
     * Get processingFinishedAt
     *
     * @return \DateTime 
     */
    public function getProcessingFinishedAt()
    {
        return $this->processingFinishedAt;
    }

    /**
     * Set notificationsCount
     *
     * @param int $notificationsCount
     * @return Newsletter
     */
    public function setNotificationsCount($notificationsCount)
    {
        $this->notificationsCount = $notificationsCount;
        return $this;
    }

    /**
     * Get notificationsCount
     *
     * @return int
     */
    public function getNotificationsCount()
    {
        return $this->notificationsCount;
    }
    

    public function startProcessing()
    {
        $this->setState(State::IN_PROGRESS);
        $this->setProcessingStartedAt(new \DateTime('now'));
    }

    public function stopProcessing($success = true, $notificationsCount = 0, $errorMsg = '')
    {
        if ($success) {
            $this->setState(State::PROCESSED);
        } else {
            $this->setState(State::CANCELLED);
        }
        $this->setProcessingFinishedAt(new \DateTime('now'));
    }

    public function isEditable()
    {
        return in_array($this->getState(), [ State::TEMPORARY, State::QUEUED ]);
    }
}