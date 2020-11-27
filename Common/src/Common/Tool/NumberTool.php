<?php

namespace Common\Tool;

class NumberTool
{
    const SCALE = 50;

    /**
     * Performs addition
     * NumberTool::add('2.71', '3.18') //5.89
     * @param string $op1
     * @param string $op2
     * @param boolean $round
     * @return string
     */
    public static function add($op1, $op2, $round = true)
    {
        $res = bcadd($op1, $op2, self::SCALE);
        return $round ? self::round($res) : $res;
    }

    /**
     * Performs substraction
     * NumberTool::sub('5.89', '3.18') //2.71
     * @param string $op1
     * @param string $op2
     * @param boolean $round
     * @return string
     */
    public static function sub($op1, $op2, $round = true)
    {
        $res = bcsub($op1, $op2, self::SCALE);
        return $round ? self::round($res) : $res;
    }

    /**
     * Performs multiplication 
     * NumberTool::mul('16.69', '12.47') //208.12
     * @param string $op1
     * @param string $op2
     * @param boolean $round
     * @return string
     */
    public static function mul($op1, $op2, $round = true)
    {
        $res = bcmul($op1, $op2, self::SCALE);
        return $round ? self::round($res) : $res;
    }

    /**
     * Performs division
     * NumberTool::div('208.12', '16.69') //12.47
     * @param string $op1
     * @param string $op2
     * @param boolean $round
     * @return string
     */
    public static function div($op1, $op2, $round = true)
    {
        $res = bcdiv($op1, $op2, self::SCALE);
        return $round ? self::round($res) : $res;
    }

    /**
     * Truncates decimal number to given precision
     * NumberTool::truncate('1.9999', 2) //1.99
     * @param string $number
     * @param integer $precision
     * @return string
     */
    public static function truncate($number, $precision)
    {
        $x = explode('.', $number);
        if (count($x) === 1) {
            return $x[0];
        }
        if ($precision === 0) {
            return $x[0];
        }
        return $x[0] . '.' . substr($x[1], 0, $precision);
    }

    /**
     * Absolute number value 
     * NumberTool::abs('-10.99') //10.99
     * @param string $number
     * @return string
     */
    public static function abs($number)
    {
        $number = (string)$number;
        if (strlen($number) === 0) {
            return $number;
        }

        if ($number[0] !== '-') {
            return $number;
        }

        return substr($number, 1);
    }
    
    /**
     * Rise $left to $right
     * @param string $left left operand
     * @param string $right right operand
     * @param boolean $round
     * @return string
     */
    public static function pow($left, $right, $round = true)
    {
        //bcpow does not support decimal numbers
        $res = (string)pow($left, $right);
        return $round ? self::round($res) : $res;
    }

    /**
     * Rounds number with precision of $precision decimal places
     * NumberTool::round('208.1243') //208.12
     * @param string $val
     * @param integer $precision
     * @return string
     */
    public static function round($val, $precision = 2)
    {
        return number_format(round($val, $precision), $precision, '.', '');
    }

    /**
     * Rounds down number with precision of $precision decimal places
     * NumberTool::roundDown('2.03717') //2.03
     * @param string $val
     * @param integer $precision
     * @return string
     */
    public static function roundDown($val, $precision = 2)
    {
        $half = 0.5 / pow(10, $precision);
        return number_format(round($val - $half, $precision), $precision, '.', '');
    }
    
    /**
     * Floor
     * @param string $val
     * @return string
     */
    public static function floor($val)
    {
        return self::truncate($val, 0);
    }
    
    /**
     * Calculates percentage
     * NumberTool::percent('19.99', '21.00') //4.20
     * @param string $amount
     * @param string $percentage
     * @param boolean $round
     * @return string
     */
    public static function percent($amount, $percentage, $round = true)
    {   
        $res = bcmul($amount, bcdiv($percentage, '100', self::SCALE), self::SCALE);
        return $round ? self::round($res) : $res;
    }
    
    /**
     * NumberTool::addPercent('19.99', '21.00') //24.19
     * @param string $amount
     * @param string $percentage
     * @return string
     */
    public static function addPercent($amount, $percentage, $round = true)
    {   
        $res = bcadd($amount, self::percent($amount, $percentage), self::SCALE);
        return $round ? self::round($res) : $res;
    }
    
    /**
     * NumberTool::beforePercentAddition('24.19', '21.00') //19.99
     * @param string $result
     * @param string $percentage
     * @return string
     */
    public static function beforePercentAddition($result, $percentage, $round = true)
    {   
        // ($result / ($percentage + 100)) * 100;
        $res = bcmul(bcdiv($result, bcadd($percentage, '100', self::SCALE), self::SCALE), '100', self::SCALE);
        return $round ? self::round($res) : $res;
    }

    public static function percentageValue($value, $total, $round = true, $floor = false)
    {
        $res = self::div($value, $total, $round);
        $res = self::mul($res, 100, $round);

        if ($round) {
            $res = self::round($res);
        }

        if ($floor) {
            return self::floor($res);
        }

        return $res;
    }

}
