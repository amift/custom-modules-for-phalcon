<?php

namespace Communication\Tool;

class NotificationState
{

    /**
     * State constants
     */
    const STATUS_NEW            = 1;
    const STATUS_SENT           = 2;
    const STATUS_ERROR          = 3;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATUS_NEW,
            self::STATUS_SENT,
            self::STATUS_ERROR,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATUS_NEW => 'label-default',
            self::STATUS_SENT => 'label-success',
            self::STATUS_ERROR => 'label-danger',
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
            self::STATUS_NEW => 'New',
            self::STATUS_SENT => 'Sent',
            self::STATUS_ERROR => 'Error',
        ];
    }

}
