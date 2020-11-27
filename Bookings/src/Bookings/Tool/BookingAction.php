<?php

namespace Bookings\Tool;

use Bookings\Tool\BookingType;

class BookingAction
{

    /**
     * Action income constants
     */
    const ACTION_INCOME_BANNERS             = 'in.banners';
    const ACTION_INCOME_SPONSORS            = 'in.sponsors';
    const ACTION_INCOME_UNDEFINED           = 'in.undefined';

    /**
     * Action expenses constants
     */
    const ACTION_EXPENSE_WITHDRAW           = 'ex.withdraws';
    const ACTION_EXPENSE_ADVERTISEMENT      = 'ex.advertisement';
    const ACTION_EXPENSE_SERVER             = 'ex.server';
    const ACTION_EXPENSE_UNDEFINED          = 'ex.undefined';

    /**
     * Get all actions keys.
     * 
     * @access public
     * @return array
     */
    public static function getActions($type = 0)
    {
        $list = [];

        if (in_array((int)$type, [0, BookingType::TYPE_INCOME])) {
            $list[] = self::ACTION_INCOME_BANNERS;
            $list[] = self::ACTION_INCOME_SPONSORS;
            $list[] = self::ACTION_INCOME_UNDEFINED;
        }

        if (in_array((int)$type, [0, BookingType::TYPE_EXPENSES])) {
            $list[] = self::ACTION_EXPENSE_WITHDRAW;
            $list[] = self::ACTION_EXPENSE_ADVERTISEMENT;
            $list[] = self::ACTION_EXPENSE_SERVER;
            $list[] = self::ACTION_EXPENSE_UNDEFINED;
        }

        return $list;
    }

    /**
     * Get all actions labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels($type = 0)
    {
        $list = [];

        if (in_array((int)$type, [0, BookingType::TYPE_INCOME])) {
            $list[self::ACTION_INCOME_BANNERS] = 'Banner incomes';
            $list[self::ACTION_INCOME_SPONSORS] = 'Sponsor incomes';
            $list[self::ACTION_INCOME_UNDEFINED] = 'Undefined incomes';
        }

        if (in_array((int)$type, [0, BookingType::TYPE_EXPENSES])) {
            $list[self::ACTION_EXPENSE_WITHDRAW] = 'Withdraw expenses';
            $list[self::ACTION_EXPENSE_ADVERTISEMENT] = 'Advertisement expenses';
            $list[self::ACTION_EXPENSE_SERVER] = 'Server expenses';
            $list[self::ACTION_EXPENSE_UNDEFINED] = 'Undefined expenses';
        }

        return $list;
    }
    
    public static function getLabelsAsOptGroup()
    {
        $list = [];

        $list['Income'][self::ACTION_INCOME_BANNERS] = 'Banner incomes';
        $list['Income'][self::ACTION_INCOME_SPONSORS] = 'Sponsor incomes';
        $list['Income'][self::ACTION_INCOME_UNDEFINED] = 'Undefined incomes';

        $list['Expenses'][self::ACTION_EXPENSE_WITHDRAW] = 'Withdraw expenses';
        $list['Expenses'][self::ACTION_EXPENSE_ADVERTISEMENT] = 'Advertisement expenses';
        $list['Expenses'][self::ACTION_EXPENSE_SERVER] = 'Server expenses';
        $list['Expenses'][self::ACTION_EXPENSE_UNDEFINED] = 'Undefined expenses';

        return $list;
    }

}
