<?php

namespace System\Tool;

class CronJobState
{

    /**
     * State constants
     */
    const STATUS_SUCCESS = 1;
    const STATUS_RUNNING = 2;
    const STATUS_ERROR   = 3;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATUS_SUCCESS,
            self::STATUS_RUNNING,
            self::STATUS_ERROR,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATUS_SUCCESS => 'label-success',
            self::STATUS_RUNNING => 'label-info',//default
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
            self::STATUS_SUCCESS => 'Finished',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_ERROR => 'Error',
        ];
    }

    public static function getLabel($status = 0)
    {
        $labels = self::getLabels();

        $label = isset($labels[$status]) ? $labels[$status] : '-';

        return $label;
    }

}
