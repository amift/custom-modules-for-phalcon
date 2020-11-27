<?php

namespace Communication\Tool;

class NewsletterType
{
    /**
     * Type constants
     */
    const CUSTOM = 1;
    const WEEKLY = 2;

    /**
     * Get all types keys.
     *
     * @access public
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::CUSTOM,
            self::WEEKLY,
        ];
    }

    /**
     * Get all types labels.
     *
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::CUSTOM => 'Custom',
            self::WEEKLY => 'Weekly',
        ];
    }

    /**
     * Get type label
     *
     * @access public
     * @param smallint $type
     * @return string
     */
    public static function getLabel($type)
    {
        $labels = self::getLabels();

        if (isset($labels[$type])) {
            return $labels[$type];
        }

        return '-';
    }
}