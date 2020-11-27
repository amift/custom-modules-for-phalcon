<?php

namespace Communication\Tool;

class NewsletterState
{
    /**
     * State constants
     */
    const TEMPORARY = 1;
    const QUEUED = 2;
    const IN_PROGRESS = 3;
    const PROCESSED = 4;
    const CANCELLED = 5;

    /**
     * Get all states keys.
     *
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::TEMPORARY,
            self::QUEUED,
            self::IN_PROGRESS,
            self::PROCESSED,
            self::CANCELLED,
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::TEMPORARY => 'label-default',
            self::QUEUED => 'label-warning',
            self::IN_PROGRESS => 'label-info',
            self::PROCESSED => 'label-success',
            self::CANCELLED => 'label-danger',
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
            self::TEMPORARY => 'Temporary',
            self::QUEUED => 'Queued',
            self::IN_PROGRESS => 'In Progress',
            self::PROCESSED => 'Processed',
            self::CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get all states labels.
     *
     * @access public
     * @return array
     */
    public static function getFormLabels($current = '')
    {
        if ((int)$current === self::QUEUED) {
            return [
                self::QUEUED => 'Queued',
                self::CANCELLED => 'Cancelled',
            ];
        }

        if ((int)$current === self::IN_PROGRESS) {
            return [
                self::IN_PROGRESS => 'In Progress',
                //self::CANCELLED => 'Cancelled',
            ];
        }

        if ((int)$current === self::PROCESSED) {
            return [ self::PROCESSED => 'Processed' ];
        }

        if ((int)$current === self::CANCELLED) {
            return [ self::CANCELLED => 'Cancelled' ];
        }

        return [
            self::TEMPORARY => 'Temporary',
            self::QUEUED => 'Queued',
            self::CANCELLED => 'Cancelled',
        ];
    }
}