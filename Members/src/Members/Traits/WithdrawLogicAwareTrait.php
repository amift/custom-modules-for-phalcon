<?php

namespace Members\Traits;

use Members\Tool\WithdrawState;
use Members\Tool\WithdrawType;

trait WithdrawLogicAwareTrait
{

    public function isPending()
    {
        return (int)$this->state === (int)WithdrawState::STATE_PENDING ? true : false;
    }

    public function isTransferMoney()
    {
        return (int)$this->state === (int)WithdrawState::STATE_TRANSFER_MONEY ? true : false;
    }

    public function isCompleted()
    {
        return (int)$this->state === (int)WithdrawState::STATE_COMPLETED ? true : false;
    }

    public function isRejected()
    {
        return (int)$this->state === (int)WithdrawState::STATE_REJECTED ? true : false;
    }

    public function isTypePayPal()
    {
        return (int)$this->type === (int)WithdrawType::TYPE_PAYPAL ? true : false;
    }

    public function isTypeBankTransfer()
    {
        return (int)$this->type === (int)WithdrawType::TYPE_BANK_TRANSFER ? true : false;
    }

    public function getTypeLabel()
    {
        $labels = WithdrawType::getLabels();
        
        return isset($labels[$this->type]) ? $labels[$this->type] : $this->type;
    }

    public function getBankNameLabel()
    {
        $labels = WithdrawType::getBanksLabels();
        
        return isset($labels[$this->bankName]) ? $labels[$this->bankName] : $this->bankName;
    }

    public function getStateLabel()
    {
        $labels = [
            WithdrawState::STATE_PENDING => 'Pending',
            WithdrawState::STATE_TRANSFER_MONEY => 'Pending',
            WithdrawState::STATE_COMPLETED => 'Paid',
            WithdrawState::STATE_REJECTED => 'Cancelled',
        ];

        if (isset($labels[$this->state])) {
            return $labels[$this->state];
        }

        return $this->state;
    }

    public function getFrontendStateClass()
    {
        $classes = [
            WithdrawState::STATE_PENDING => 'pending',
            WithdrawState::STATE_TRANSFER_MONEY => 'pending',
            WithdrawState::STATE_COMPLETED => 'published',
            WithdrawState::STATE_REJECTED => 'cancelled',
        ];

        if (isset($classes[$this->state])) {
            return $classes[$this->state];
        }
        
        return 'pending';
    }

}
