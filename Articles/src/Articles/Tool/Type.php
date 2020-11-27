<?php

namespace Articles\Tool;

class Type
{

    /**
     * Type constants
     */
    const TYPE_NEWS = 1;
    const TYPE_VIDEO = 2;

    /**
     * Get all types keys.
     * 
     * @access public
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_NEWS,
            self::TYPE_VIDEO,
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
            self::TYPE_NEWS => 'News',
            self::TYPE_VIDEO => 'Video',
        ];
    }

    public static function getLabel($type)
    {
        $labels = self::getLabels();

        $label = isset($labels[$type]) ? $labels[$type] : '=';

        return $label;
    }

}
