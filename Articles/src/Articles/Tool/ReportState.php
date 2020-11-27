<?php

namespace Articles\Tool;

class ReportState
{

    /**
     * State constants
     */
    const STATE_NEW         = 1;
    const STATE_ACCEPTED    = 2;
    const STATE_IGNORED     = 3;

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
            self::STATE_ACCEPTED,
            self::STATE_IGNORED,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_NEW => 'label-default',
            self::STATE_ACCEPTED => 'label-success',
            self::STATE_IGNORED => 'label-warning',
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
            self::STATE_ACCEPTED => 'Accepted',
            self::STATE_IGNORED => 'Ignored',
        ];
    }

}
