<?php

namespace Statistics\Traits;

use Common\Traits\OrderingEntityTrait;
use Statistics\Traits\SportLeagueEntityTrait;
use Statistics\Traits\SportLeagueGroupEntityTrait;
use Statistics\Traits\SportSeasonEntityTrait;
use Statistics\Traits\SportTeamEntityTrait;
use Statistics\Traits\SportTypeEntityTrait;

trait StatsDataEntityTrait
{
    use SportTypeEntityTrait;
    use SportLeagueEntityTrait;
    use SportLeagueGroupEntityTrait;
    use SportSeasonEntityTrait;
    use SportTeamEntityTrait;
    use OrderingEntityTrait;

    /**
     * @var string
     * @ORM\Column(name="`place`", type="string", length=10, nullable=true)
     */
    private $place;

    /**
     * @var string
     * @ORM\Column(name="`matches`", type="string", length=10, nullable=true)
     */
    private $matches;

    /**
     * @var string
     * @ORM\Column(name="`win`", type="string", length=10, nullable=true)
     */
    private $win;

    /**
     * @var string
     * @ORM\Column(name="`win_ot`", type="string", length=10, nullable=true)
     */
    private $winOt;

    /**
     * @var string
     * @ORM\Column(name="`draws`", type="string", length=10, nullable=true)
     */
    private $draws;

    /**
     * @var string
     * @ORM\Column(name="`lose`", type="string", length=10, nullable=true)
     */
    private $lose;

    /**
     * @var string
     * @ORM\Column(name="`lose_ot`", type="string", length=10, nullable=true)
     */
    private $loseOt;

    /**
     * @var string
     * @ORM\Column(name="`score`", type="string", length=10, nullable=true)
     */
    private $score;

    /**
     * @var string
     * @ORM\Column(name="`goals`", type="string", length=10, nullable=true)
     */
    private $goals;

    /**
     * @var string
     * @ORM\Column(name="`goals_avg`", type="string", length=10, nullable=true)
     */
    private $goalsAvg;

    /**
     * @var string
     * @ORM\Column(name="`miss`", type="string", length=10, nullable=true)
     */
    private $miss;

    /**
     * @var string
     * @ORM\Column(name="`miss_avg`", type="string", length=10, nullable=true)
     */
    private $missAvg;

    /**
     * @var string
     * @ORM\Column(name="`different`", type="string", length=10, nullable=true)
     */
    private $different;

    /**
     * @var string
     * @ORM\Column(name="`win_prc`", type="string", length=10, nullable=true)
     */
    private $winPrc;

    /**
     * @var string
     * @ORM\Column(name="`home`", type="string", length=10, nullable=true)
     */
    private $home;

    /**
     * @var string
     * @ORM\Column(name="`away`", type="string", length=10, nullable=true)
     */
    private $away;

    /**
     * Set place
     *
     * @param string $place
     * @return $this
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set matches
     *
     * @param string $matches
     * @return $this
     */
    public function setMatches($matches)
    {
        $this->matches = $matches;

        return $this;
    }

    /**
     * Get matches
     *
     * @return string
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Set win
     *
     * @param string $win
     * @return $this
     */
    public function setWin($win)
    {
        $this->win = $win;

        return $this;
    }

    /**
     * Get win
     *
     * @return string
     */
    public function getWin()
    {
        return $this->win;
    }

    /**
     * Set winOt
     *
     * @param string $winOt
     * @return $this
     */
    public function setWinOt($winOt)
    {
        $this->winOt = $winOt;

        return $this;
    }

    /**
     * Get winOt
     *
     * @return string
     */
    public function getWinOt()
    {
        return $this->winOt;
    }

    /**
     * Set draws
     *
     * @param string $draws
     * @return $this
     */
    public function setDraws($draws)
    {
        $this->draws = $draws;

        return $this;
    }

    /**
     * Get draws
     *
     * @return string
     */
    public function getDraws()
    {
        return $this->draws;
    }

    /**
     * Set lose
     *
     * @param string $lose
     * @return $this
     */
    public function setLose($lose)
    {
        $this->lose = $lose;

        return $this;
    }

    /**
     * Get lose
     *
     * @return string
     */
    public function getLose()
    {
        return $this->lose;
    }

    /**
     * Set loseOt
     *
     * @param string $loseOt
     * @return $this
     */
    public function setLoseOt($loseOt)
    {
        $this->loseOt = $loseOt;

        return $this;
    }

    /**
     * Get loseOt
     *
     * @return string
     */
    public function getLoseOt()
    {
        return $this->loseOt;
    }

    /**
     * Set score
     *
     * @param string $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return string
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set goals
     *
     * @param string $goals
     * @return $this
     */
    public function setGoals($goals)
    {
        $this->goals = $goals;

        return $this;
    }

    /**
     * Get goals
     *
     * @return string
     */
    public function getGoals()
    {
        return $this->goals;
    }

    /**
     * Set goalsAvg
     *
     * @param string $goalsAvg
     * @return $this
     */
    public function setGoalsAvg($goalsAvg)
    {
        $this->goalsAvg = $goalsAvg;

        return $this;
    }

    /**
     * Get goalsAvg
     *
     * @return string
     */
    public function getGoalsAvg()
    {
        return $this->goalsAvg;
    }

    /**
     * Set miss
     *
     * @param string $miss
     * @return $this
     */
    public function setMiss($miss)
    {
        $this->miss = $miss;

        return $this;
    }

    /**
     * Get miss
     *
     * @return string
     */
    public function getMiss()
    {
        return $this->miss;
    }

    /**
     * Set missAvg
     *
     * @param string $missAvg
     * @return $this
     */
    public function setMissAvg($missAvg)
    {
        $this->missAvg = $missAvg;

        return $this;
    }

    /**
     * Get missAvg
     *
     * @return string
     */
    public function getMissAvg()
    {
        return $this->missAvg;
    }

    /**
     * Set different
     *
     * @param string $different
     * @return $this
     */
    public function setDifferent($different)
    {
        $this->different = $different;

        return $this;
    }

    /**
     * Get different
     *
     * @return string
     */
    public function getDifferent()
    {
        return $this->different;
    }

    /**
     * Set winPrc
     *
     * @param string $winPrc
     * @return $this
     */
    public function setWinPrc($winPrc)
    {
        $this->winPrc = $winPrc;

        return $this;
    }

    /**
     * Get winPrc
     *
     * @return string
     */
    public function getWinPrc()
    {
        return $this->winPrc;
    }

    /**
     * Set home
     *
     * @param string $home
     * @return $this
     */
    public function setHome($home)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * Get home
     *
     * @return string
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Set away
     *
     * @param string $away
     * @return $this
     */
    public function setAway($away)
    {
        $this->away = $away;

        return $this;
    }

    /**
     * Get away
     *
     * @return string
     */
    public function getAway()
    {
        return $this->away;
    }

}
