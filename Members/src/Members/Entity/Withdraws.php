<?php

namespace Members\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\CreatedFromIpEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\UserAgentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Members\Entity\Member;
use Members\Tool\WithdrawState;
use Members\Tool\WithdrawType;
use Members\Traits\WithdrawLogicAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Members\Repository\WithdrawsRepository")
 * @ORM\Table(
 *      name="members_withdraws",
 *      indexes={
 *          @ORM\Index(name="members_withdraws_state_idx", columns={"state"}),
 *          @ORM\Index(name="members_withdraws_type_idx", columns={"type"}),
 *          @ORM\Index(name="members_withdraws_trans_no_idx", columns={"transaction_number"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Withdraws 
{
    use ObjectSimpleHydrating;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use CreatedFromIpEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;
    use WithdrawLogicAwareTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Members\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $member;

    /**
     * @var smallint
     * @ORM\Column(name="`state`", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $state = WithdrawState::STATE_PENDING;

    /**
     * @var integer
     * @ORM\Column(name="`pts`", type="integer", nullable=false, options={"unsigned":true})
     */
    private $pts;

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
     * @var smallint
     * @ORM\Column(name="`type`", type="smallint", nullable=false, options={"unsigned":true, "default":1})
     */
    private $type = WithdrawType::TYPE_PAYPAL;

    /**
     * @var string
     * @ORM\Column(name="paypal_email", type="string", nullable=true)
     */
    private $paypalEmail;

    /**
     * @var string
     * @ORM\Column(name="bank_name", type="string", nullable=true)
     */
    private $bankName;

    /**
     * @var string
     * @ORM\Column(name="bank_account", type="string", nullable=true)
     */
    private $bankAccount;

    /**
     * @var string
     * @ORM\Column(name="receiver_name", type="string", nullable=true)
     */
    private $receiverName;

    /**
     * @var string
     * @ORM\Column(name="transaction_number", type="string", nullable=true)
     */
    private $transactionNumber;

    /**
     * @var \DateTime
     * @ORM\Column(name="transaction_date", type="datetime", nullable=true)
     */
    private $transactionDate;

    /**
     * @var string
     * @ORM\Column(name="reason", type="string", nullable=true)
     */
    private $reason;

    /**
     * Class constructor
     */
    public function __construct() 
    {
        $this->setState(WithdrawState::STATE_PENDING);
        $this->setCurrency('EUR');
        $this->setType(WithdrawType::TYPE_PAYPAL);
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
     * Set related member object 
     *
     * @param Member $member
     * @return Withdraws
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get related member object 
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set status
     *
     * @param smallint $state
     * @return Withdraws
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
     * Set pts 
     *
     * @param int $pts
     * @return Withdraws
     */
    public function setPts($pts)
    {
        $this->pts = $pts;

        return $this;
    }

    /**
     * Get pts
     *
     * @return int
     */
    public function getPts()
    {
        return $this->pts;
    }

    /**
     * Set amount
     * 
     * @param decimal $amount
     * @return Withdraws
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
     * @return Withdraws
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
     * Set type
     *
     * @param smallint $type
     * @return Withdraws
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
     * Set paypalEmail
     *
     * @param string $paypalEmail
     * @return Withdraws
     */
    public function setPaypalEmail($paypalEmail)
    {
        $this->paypalEmail = $paypalEmail;

        return $this;
    }

    /**
     * Get paypalEmail
     *
     * @return string
     */
    public function getPaypalEmail()
    {
        return $this->paypalEmail;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     * @return Withdraws
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set bankAccount
     *
     * @param string $bankAccount
     * @return Withdraws
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set receiverName
     *
     * @param string $receiverName
     * @return Withdraws
     */
    public function setReceiverName($receiverName)
    {
        $this->receiverName = $receiverName;

        return $this;
    }

    /**
     * Get receiverName
     *
     * @return string
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * Set transactionNumber
     *
     * @param string $transactionNumber
     * @return Withdraws
     */
    public function setTransactionNumber($transactionNumber)
    {
        $this->transactionNumber = $transactionNumber;

        return $this;
    }

    /**
     * Get transactionNumber
     *
     * @return string
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }

    /**
     * Set transactionDate
     *
     * @param \DateTime $transactionDate
     * @return Withdraws
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    /**
     * Get transactionDate
     *
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * Set reason
     * 
     * @param string $reason
     * @return Withdraws
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    public function unsetTransactionNumber()
    {
        $this->transactionNumber = null;

        return $this;
    }

    public function unsetTransactionDate()
    {
        $this->transactionDate = null;

        return $this;
    }

    public function unsetReason()
    {
        $this->reason = null;

        return $this;
    }

}
