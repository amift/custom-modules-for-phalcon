<?php

namespace Statistics\Traits;

use Statistics\Entity\SportType;

trait SportTypeEntityTrait
{

    /**
     * @var SportType
     * @ORM\ManyToOne(targetEntity="Statistics\Entity\SportType")
     * @ORM\JoinColumn(name="sport_type_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sportType;

    /**
     * Set sportType
     * 
     * @param SportType $sportType
     * @return $this
     */
    public function setSportType($sportType)
    {
        $this->sportType = $sportType;

        return $this;
    }

    /**
     * Get sportType
     * 
     * @return SportType
     */
    public function getSportType()
    {
        return $this->sportType;
    }

    /**
     * Unset sportType
     * 
     * @return $this
     */
    public function unsetSportType()
    {
        $this->sportType = null;

        return $this;
    }

}
