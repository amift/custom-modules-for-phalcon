<?php

namespace Statistics\Traits;

use Statistics\Entity\SportTeam;

trait SportTeamEntityTrait
{

    /**
     * @var SportTeam
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportTeam")
     * @ORM\JoinColumn(name="sport_team_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sportTeam;

    /**
     * Set sportTeam
     * 
     * @param SportTeam $sportTeam
     * @return $this
     */
    public function setSportTeam($sportTeam)
    {
        $this->sportTeam = $sportTeam;

        return $this;
    }

    /**
     * Get sportTeam
     * 
     * @return SportTeam
     */
    public function getSportTeam()
    {
        return $this->sportTeam;
    }

    /**
     * Unset sportTeam
     * 
     * @return $this
     */
    public function unsetSportTeam()
    {
        $this->sportTeam = null;

        return $this;
    }

}
