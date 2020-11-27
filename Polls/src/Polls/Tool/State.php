<?php

namespace Polls\Tool;

class State
{

    /**
     * State constants
     */
    const STATE_PENDING     = 1;
    const STATE_ACTIVE      = 2;
    const STATE_ARCHIVED    = 3;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATE_PENDING,
            self::STATE_ACTIVE,
            self::STATE_ARCHIVED,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_PENDING => 'label-warning',
            self::STATE_ACTIVE => 'label-success',
            self::STATE_ARCHIVED => 'label-default',
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
            self::STATE_PENDING => 'Pending',
            self::STATE_ACTIVE => 'Active',
            self::STATE_ARCHIVED => 'Archived',
        ];
    }

    public static function getOutputStates()
    {
        return [
            self::STATE_ACTIVE
        ];
    }

}
