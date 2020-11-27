<?php

namespace Members\Tool;

class State
{

    /**
     * State constants
     */
    const STATE_ACTIVE          = 1;
    const STATE_INACTIVE        = 2;
    const STATE_BLOCKED         = 3;
    const STATE_BANNED          = 4;

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
            self::STATE_BLOCKED,
            self::STATE_BANNED,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_ACTIVE => 'label-success',
            self::STATE_BLOCKED => 'label-danger',
            self::STATE_INACTIVE => 'label-default',
            self::STATE_BANNED => 'label-warning',
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
            self::STATE_ACTIVE => 'Active',
            self::STATE_INACTIVE => 'Inactive',
            self::STATE_BLOCKED => 'Blocked',
            self::STATE_BANNED => 'Banned',
        ];
    }

}
