<?php

namespace Bookings\Entity;

use Bookings\Tool\BookingType;
use Bookings\Traits\BookingLogicAwareTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Bookings\Repository\BookingRepository")
 * @ORM\Table(
 *      name="bookings",
 *      indexes={
 *          @ORM\Index(name="bookings_type_idx", columns={"type"}),
 *          @ORM\Index(name="bookings_action_idx", columns={"action"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Booking 
{
    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use BookingLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="`type`", type="smallint", nullable=false, options={"unsigned":true, "default":1})
     */
    private $type;

    /** 
     * @var string
     * @ORM\Column(name="action", type="string", length=50, nullable=false)
     */
    private $action;

    /**
     * @var decimal
     * @ORM\Column(name="`amount`", type="decimal", precision=15, scale=2, nullable=true)
     */
    private $amount;

    /** 
     * @var string
     * @ORM\Column(name="`currency`", type="string", length=3, nullable=true)
     */
    private $currency;

    /**
     * @var \DateTime
     * @ORM\Column(name="`date`", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(name="`comment`", type="string", nullable=true)
     */
    private $comment;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->setCurrency('EUR');
        $this->setTypeAsIncome();
        $this->setDateAsCurrent();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->id);
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
     * @return Booking
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
     * Set action
     * 
     * @param string $action
     * @return Booking
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set amount
     * 
     * @param decimal $amount
     * @return Booking
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     * 
     * @return decimal
     */
    public function getAmount() 
    {
        return $this->amount;
    }

    /**
     * Set currency
     * 
     * @param string $currency
     * @return Booking
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Booking
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set comment
     * 
     * @param string $comment
     * @return Booking
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

}
