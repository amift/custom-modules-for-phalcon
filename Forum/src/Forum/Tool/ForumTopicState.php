<?php

namespace Forum\Tool;

class ForumTopicState
{

    /**
     * State constants
     */
    const STATE_NEW         = 1;
    const STATE_ACTIVE      = 2;
    const STATE_INACTIVE    = 3;
    const STATE_BLOCKED     = 4;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATE_NEW,
            self::STATE_ACTIVE,
            self::STATE_INACTIVE,
            self::STATE_BLOCKED,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_NEW => 'label-warning',
            self::STATE_ACTIVE => 'label-success',
            self::STATE_INACTIVE => 'label-default',
            self::STATE_BLOCKED => 'label-danger',
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
            self::STATE_NEW => 'New',
            self::STATE_ACTIVE => 'Active',
            self::STATE_INACTIVE => 'Inactive',
            self::STATE_BLOCKED => 'Blocked',
        ];
    }

    public static function getOutputStates()
    {
        return [
            self::STATE_NEW,
            self::STATE_ACTIVE
        ];
    }

    public static function getHideStates()
    {
        return [
            self::STATE_INACTIVE,
            self::STATE_BLOCKED
        ];
    }

}
