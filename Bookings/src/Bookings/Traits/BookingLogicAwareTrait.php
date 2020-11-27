<?php

namespace Bookings\Traits;

use Bookings\Tool\BookingAction;
use Bookings\Tool\BookingType;

trait BookingLogicAwareTrait
{

    public function isTypeIncome()
    {
        return (int)$this->type === (int)BookingType::TYPE_INCOME ? true : false;
    }

    public function isTypeExpenses()
    {
        return (int)$this->type === (int)BookingType::TYPE_EXPENSES ? true : false;
    }

    public function getTypeLabel()
    {
        $labels = BookingType::getLabels();

        return isset($labels[$this->type]) ? $labels[$this->type] : $this->type;
    }

    public function getActionLabel()
    {
        $labels = BookingAction::getLabels();

        return isset($labels[$this->action]) ? $labels[$this->action] : $this->action;
    }

    public function setTypeAsIncome()
    {
        $this->setType(BookingType::TYPE_INCOME);
        $this->setActionAsIncomeUndefined();

        return $this;
    }

    public function setTypeAsExpenses()
    {
        $this->setType(BookingType::TYPE_EXPENSES);
        $this->setActionAsExpensesUndefined();

        return $this;
    }

    public function setActionAsIncomeBanners()
    {
        $this->setAction(BookingAction::ACTION_INCOME_BANNERS);

        return $this;
    }

    public function setActionAsIncomeSponsors()
    {
        $this->setAction(BookingAction::ACTION_INCOME_SPONSORS);

        return $this;
    }

    public function setActionAsIncomeUndefined()
    {
        $this->setAction(BookingAction::ACTION_INCOME_UNDEFINED);

        return $this;
    }

    public function setActionAsExpensesWithdraw()
    {
        $this->setAction(BookingAction::ACTION_EXPENSE_WITHDRAW);

        return $this;
    }

    public function setActionAsExpensesAdvertisement()
    {
        $this->setAction(BookingAction::ACTION_EXPENSE_ADVERTISEMENT);

        return $this;
    }

    public function setActionAsExpensesServer()
    {
        $this->setAction(BookingAction::ACTION_EXPENSE_SERVER);

        return $this;
    }

    public function setActionAsExpensesUndefined()
    {
        $this->setAction(BookingAction::ACTION_EXPENSE_UNDEFINED);

        return $this;
    }

    public function setDateAsCurrent()
    {
        $this->setDate(new \DateTime('now'));

        return $this;
    }

}
