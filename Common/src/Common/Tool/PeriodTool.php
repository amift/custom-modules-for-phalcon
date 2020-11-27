<?php

namespace Common\Tool;

class PeriodTool
{
    const WEEK_1 = '1_week';
    const WEEK_2 = '2_weeks';
    const WEEK_3 = '3_weeks';
    const MONTH_1 = '1_month';
    const MONTH_2 = '2_months';
    const MONTH_3 = '3_months';
    const MONTH_4 = '4_months';
    const MONTH_5 = '5_months';
    const MONTH_6 = '6_months';
    const MONTH_7 = '7_months';
    const MONTH_8 = '8_months';
    const MONTH_9 = '9_months';
    const MONTH_10 = '10_months';
    const MONTH_11 = '11_months';
    const MONTH_12 = '12_months';
    const YEAR_MORE = 'more_than_year';

    public static function options()
    {
        return [
            self::WEEK_1 => 'Today till 1 week ago',
            self::WEEK_2 => '1 week till 2 weeks ago',
            self::WEEK_3 => '2 weeks till 3 weeks ago',
            self::MONTH_1 => '3 weeks till 1 month ago',
            self::MONTH_2 => '1 month till 2 months ago',
            self::MONTH_3 => '2 months till 3 months ago',
            self::MONTH_4 => '3 months till 4 months ago',
            self::MONTH_5 => '4 months till 5 months ago',
            self::MONTH_6 => '5 months till 6 months ago',
            self::MONTH_7 => '6 months till 7 months ago',
            self::MONTH_8 => '7 months till 8 months ago',
            self::MONTH_9 => '8 months till 9 months ago',
            self::MONTH_10 => '9 months till 10 months ago',
            self::MONTH_11 => '10 months till 11 months ago',
            self::MONTH_12 => '11 months till 1 year',
            self::YEAR_MORE => 'More than 1 year ago',
        ];
    }

    public static function keys($all = true)
    {
        $keys = [
            self::WEEK_1,
            self::WEEK_2,
            self::WEEK_3,
            self::MONTH_1,
            self::MONTH_2,
            self::MONTH_3,
            self::MONTH_4,
            self::MONTH_5,
            self::MONTH_6,
            self::MONTH_7,
            self::MONTH_8,
            self::MONTH_9,
            self::MONTH_10,
            self::MONTH_11,
            self::MONTH_12,
        ];

        if ($all) {
            $keys[] = self::YEAR_MORE;
        }

        return $keys;
    }

    public static function periodDates($key = '')
    {
        $till = new \DateTime('now');

        if ($key === self::YEAR_MORE) {
            $till->modify('-1 year');

            return [ null, $till ];
        }

        if (!in_array($key, self::keys(false))) {
            return [ null, null ];
        }

        $p = explode('_', $key, 2);

        $from = clone $till;
        $from->modify('-' . $p[0] . ' ' . $p[1]);

        if ((int)$p[0] > 1) {
            $count = $p[0] - 1;
            $unit = $p[1] === 'months' ? 'month' : 'week';
            $till->modify('-' . $count . ' ' . $unit . ($count > 1 ? 's' : ''));
        } elseif ((int)$p[0] === 1 && (string)$p[1] === 'month') {
            $till->modify('-3 weeks');
        }

        return [ $from, $till ];
    }
}