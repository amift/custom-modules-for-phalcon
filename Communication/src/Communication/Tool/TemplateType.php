<?php

namespace Communication\Tool;

class TemplateType 
{

    /**
     * Type constants
     */
    const TYPE_EMAIL = 1;

    /**
     * Get all types keys.
     * 
     * @access public
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_EMAIL,
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
            self::TYPE_EMAIL => 'E-mail',
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
