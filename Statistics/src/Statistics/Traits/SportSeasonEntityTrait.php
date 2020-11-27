<?php

namespace Statistics\Traits;

use Statistics\Entity\SportSeason;

trait SportSeasonEntityTrait
{

    /**
     * @var SportSeason
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportSeason")
     * @ORM\JoinColumn(name="sport_season_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sportSeason;

    /**
     * Set sportSeason
     * 
     * @param SportSeason $sportSeason
     * @return $this
     */
    public function setSportSeason($sportSeason)
    {
        $this->sportSeason = $sportSeason;

        return $this;
    }

    /**
     * Get sportSeason
     * 
     * @return SportSeason
     */
    public function getSportSeason()
    {
        return $this->sportSeason;
    }

    /**
     * Unset sportSeason
     * 
     * @return $this
     */
    public function unsetSportSeason()
    {
        $this->sportSeason = null;

        return $this;
    }

}
