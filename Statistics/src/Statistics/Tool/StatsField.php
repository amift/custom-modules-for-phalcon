<?php

namespace Statistics\Tool;

class StatsField
{

    /**
     * Fields constants
     */
    const FIELD_PLACE = 'place';
    const FIELD_MATCHES = 'matches';
    const FIELD_WIN = 'win';
    const FIELD_WIN_OT = 'winOt';
    const FIELD_DRAWS = 'draws';
    const FIELD_LOSE = 'lose';
    const FIELD_LOSE_OT = 'loseOt';
    const FIELD_SCORE = 'score';
    const FIELD_GOALS = 'goals';
    const FIELD_GOALS_AVG = 'goalsAvg';
    const FIELD_MISS = 'miss';
    const FIELD_MISS_AVG = 'missAvg';
    const FIELD_DIFFERENT = 'different';
    const FIELD_WIN_PRC = 'winPrc';
    const FIELD_HOME = 'home';
    const FIELD_AWAY = 'away';

    /**
     * Get all fields keys.
     * 
     * @access public
     * @return array
     */
    public static function getFields()
    {
        return [
            self::FIELD_PLACE,
            self::FIELD_MATCHES,
            self::FIELD_WIN,
            self::FIELD_WIN_OT,
            self::FIELD_DRAWS,
            self::FIELD_LOSE,
            self::FIELD_LOSE_OT,
            self::FIELD_SCORE,
            self::FIELD_GOALS,
            self::FIELD_GOALS_AVG,
            self::FIELD_MISS,
            self::FIELD_MISS_AVG,
            self::FIELD_DIFFERENT,
            self::FIELD_WIN_PRC,
            self::FIELD_HOME,
            self::FIELD_AWAY,
        ];
    }

    /**
     * Get all fields labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::FIELD_PLACE => 'Place',
            self::FIELD_MATCHES => 'Matches',
            self::FIELD_WIN => 'Win',
            self::FIELD_WIN_OT => 'Win OT',
            self::FIELD_DRAWS => 'Draws',
            self::FIELD_LOSE => 'Lose',
            self::FIELD_LOSE_OT => 'Lose OT',
            self::FIELD_SCORE => 'Score',
            self::FIELD_GOALS => 'Goals',
            self::FIELD_GOALS_AVG => 'Goals Avg',
            self::FIELD_MISS => 'Miss',
            self::FIELD_MISS_AVG => 'Miss Avg',
            self::FIELD_DIFFERENT => 'Different',
            self::FIELD_WIN_PRC => 'Win Percentage',
            self::FIELD_HOME => 'Home',
            self::FIELD_AWAY => 'Away',
        ];
    }

    public static function getLabel($key)
    {
        $labels = self::getLabels();

        return isset($labels[$key]) ? $labels[$key] : $key;
    }

}
