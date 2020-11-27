<?php

namespace Statistics\Traits;

use Statistics\Entity\SportLeague;

trait SportLeagueEntityTrait
{

    /**
     * @var SportLeague
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportLeague")
     * @ORM\JoinColumn(name="sport_league_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sportLeague;

    /**
     * Set sportLeague
     * 
     * @param SportLeague $sportLeague
     * @return $this
     */
    public function setSportLeague($sportLeague)
    {
        $this->sportLeague = $sportLeague;

        return $this;
    }

    /**
     * Get sportLeague
     * 
     * @return SportLeague
     */
    public function getSportLeague()
    {
        return $this->sportLeague;
    }

    /**
     * Unset sportLeague
     * 
     * @return $this
     */
    public function unsetSportLeague()
    {
        $this->sportLeague = null;

        return $this;
    }

}
