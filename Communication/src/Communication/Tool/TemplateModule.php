<?php

namespace Communication\Tool;

class TemplateModule 
{

    /**
     * Module constants
     */
    const MODULE_ARTICLES = 'articles';
    const MODULE_MANUAL = 'manual';
    const MODULE_MEMBERS = 'members';

    /**
     * Get all modules keys.
     * 
     * @access public
     * @return array
     */
    public static function getModules()
    {
        return [
            self::MODULE_ARTICLES,
            self::MODULE_MANUAL,
            self::MODULE_MEMBERS,
        ];
    }

    /**
     * Get all modules labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::MODULE_ARTICLES => 'Articles',
            self::MODULE_MANUAL => 'Manual',
            self::MODULE_MEMBERS => 'Members',
        ];
    }

    /**
     * Get module label
     * 
     * @access public
     * @param string $module
     * @return string
     */
    public static function getLabel($module)
    {
        $labels = self::getLabels();

        if (isset($labels[$module])) {
            return $labels[$module];
        }

        return '-';
    }

}
