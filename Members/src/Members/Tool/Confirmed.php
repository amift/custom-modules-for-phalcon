<?php

namespace Members\Tool;

class Confirmed
{

    /**
     * State constants
     */
    const CONFIRMED_YES       = 1;
    const CONFIRMED_NO        = 0;

    /**
     * Get all confirmed keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::CONFIRMED_YES,
            self::CONFIRMED_NO,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::CONFIRMED_YES => 'label-success',
            self::CONFIRMED_NO => 'label-warning',
        ];
    }

    /**
     * Get all confirmed full labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::CONFIRMED_YES => 'Confirmed',
            self::CONFIRMED_NO => 'Not Confirmed',
        ];
    }

    /**
     * Get all confirmed short labels.
     * 
     * @access public
     * @return array
     */
    public static function getShortLabels()
    {
        return [
            self::CONFIRMED_YES => 'Yes',
            self::CONFIRMED_NO => 'No',
        ];
    }

}
