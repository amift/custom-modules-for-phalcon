<?php

namespace Communication\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\MessageEntityTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Communication\Entity\Template;
use Communication\Tool\NotificationState as State;
use Communication\Traits\LogicAwareTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;

/**
 * @ORM\Entity(repositoryClass="Communication\Repository\NotificationRepository")
 * @ORM\Table(
 *      name="communication_notifications",
 *      indexes={
 *          @ORM\Index(name="communication_notifications_created_at_idx", columns={"created_at"}),
 *          @ORM\Index(name="communication_notifications_type_idx", columns={"type"}),
 *          @ORM\Index(name="communication_notifications_state_idx", columns={"state"}),
 *          @ORM\Index(name="communication_notifications_receiver_idx", columns={"receiver"}),
 *          @ORM\Index(name="communication_notifications_to_send_at_idx", columns={"to_send_at"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Notification 
{

    use ObjectSimpleHydrating;
    use MessageEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use LogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Template
     * @ORM\ManyToOne(targetEntity="Communication\Entity\Template")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $template;

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
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $member;

    /**
     * @var string
     * @ORM\Column(name="receiver", type="string", length=175, nullable=true)
     */
    private $receiver;
    
    /**
     * @ORM\Column(name="attachments", type="json_array", nullable=true)
     */
    private $attachments;

    /**
     * @var DateTime
     * @ORM\Column(name="to_send_at", type="datetime", nullable=true)
     */
    private $toSendAt;

    /**
     * @var DateTime
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var string
     * @ORM\Column(name="sending_error", type="string", nullable=true)
     */
    private $sendingError;

    /**
     * @ORM\Column(name="sending_details", type="json_array", nullable=true)
     */
    private $sendingDetails;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->state = State::STATUS_NEW;
        $this->attachments = [];
        $this->sendingDetails = [];
        $this->toSendAt = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return sprintf('Notification [Id: %s]', $this->id);
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
     * Set template
     * 
     * @param Template $template
     * @return Notification
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     * 
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set type
     *
     * @param smallint $type
     * @return Notification
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
     * Set state
     *
     * @param smallint $state
     * @return Notification
     */
    public function setState($state)
    {
        $this->state = $state;

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
     * Set member
     * 
     * @param Member $member
     * @return Notification
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     * 
     * @return \Members\Entity\Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set receiver
     *
     * @param string $receiver
     * @return Notification
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return string 
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set attachments
     *
     * @param array $attachments
     * @return Notification
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
     * Set toSendAt
     *
     * @param \DateTime $toSendAt
     * @return Notification
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
     * Set sentAt
     *
     * @param \DateTime $sentAt
     * @return Notification
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime 
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set sendingError
     *
     * @param string $sendingError
     * @return Notification
     */
    public function setSendingError($sendingError)
    {
        $this->sendingError = $sendingError;

        return $this;
    }

    /**
     * Get sendingError
     *
     * @return string 
     */
    public function getSendingError()
    {
        return $this->sendingError;
    }

    /**
     * Set sendingDetails
     *
     * @param array $sendingDetails
     * @return Notification
     */
    public function setSendingDetails($sendingDetails)
    {
        $this->sendingDetails = $sendingDetails;

        return $this;
    }

    /**
     * Get sendingDetails
     *
     * @return array 
     */
    public function getSendingDetails()
    {
        return $this->sendingDetails;
    }

}
