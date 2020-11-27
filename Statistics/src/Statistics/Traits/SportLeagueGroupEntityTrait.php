<?php

namespace Statistics\Traits;

use Statistics\Entity\SportLeagueGroup;

trait SportLeagueGroupEntityTrait
{

    /**
     * @var SportLeagueGroup
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportLeagueGroup")
     * @ORM\JoinColumn(name="sport_league_group_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sportLeagueGroup;

    /**
     * Set sportLeagueGroup
     * 
     * @param SportLeagueGroup $sportLeagueGroup
     * @return $this
     */
    public function setSportLeagueGroup($sportLeagueGroup)
    {
        $this->sportLeagueGroup = $sportLeagueGroup;

        return $this;
    }

    /**
     * Get sportLeagueGroup
     * 
     * @return SportLeagueGroup
     */
    public function getSportLeagueGroup()
    {
        return $this->sportLeagueGroup;
    }

    /**
     * Unset sportLeagueGroup
     * 
     * @return $this
     */
    public function unsetSportLeagueGroup()
    {
        $this->sportLeagueGroup = null;

        return $this;
    }

}
