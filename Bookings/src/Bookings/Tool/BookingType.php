<?php

namespace Bookings\Tool;

class BookingType
{

    /**
     * Type constants
     */
    const TYPE_INCOME = 1;
    const TYPE_EXPENSES = 2;

    /**
     * Get all types keys.
     * 
     * @access public
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_INCOME,
            self::TYPE_EXPENSES,
        ];
    }

    /**
     * Get all types labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::TYPE_INCOME => 'Income',
            self::TYPE_EXPENSES => 'Expenses',
        ];
    }

}
