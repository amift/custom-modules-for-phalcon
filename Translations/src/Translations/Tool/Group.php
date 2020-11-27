<?php

namespace Translations\Tool;

class Group
{

    const NOT_SET = 'Global';
    const MEMBERS = 'Members';
    const ARTICLES = 'Articles';
    const POLLS = 'Polls';
    const FORUM = 'Forum';
    const STATISTICS = 'Statistics';


    /**
     * Get all groups keys.
     * 
     * @access public
     * @return array
     */
    public static function getKeys()
    {
        return [
            self::NOT_SET,
            self::ARTICLES,
            self::MEMBERS,
            self::POLLS,
            self::FORUM,
            self::STATISTICS
        ];
    }

    /**
     * Get all groups.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        $list = [];

        foreach (self::getKeys() as $group) {
            $list[$group] = $group;
        }

        return $list;
    }

}
