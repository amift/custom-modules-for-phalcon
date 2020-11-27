<?php

namespace Common\Tool;

class Enable
{

    /**
     * State constants
     */
    const STATE_ACTIVE      = 1;
    const STATE_INACTIVE    = 0;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATE_ACTIVE,
            self::STATE_INACTIVE,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_ACTIVE => 'label-success',
            self::STATE_INACTIVE => 'label-default',
        ];
    }

    /**
     * Get all states labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::STATE_ACTIVE => 'Enabled',
            self::STATE_INACTIVE => 'Disabled',
        ];
    }

    /**
     * Get all states labels.
     * 
     * @access public
     * @return array
     */
    public static function getShortLabels()
    {
        return [
            self::STATE_ACTIVE => 'Yes',
            self::STATE_INACTIVE => 'No',
        ];
    }

}
