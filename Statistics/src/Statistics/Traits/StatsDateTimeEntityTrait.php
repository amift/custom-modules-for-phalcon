<?php

namespace Statistics\Traits;

use DateTime;

trait StatsDateTimeEntityTrait
{

    /**
     * @var DateTime
     * @ORM\Column(name="`date`", type="date", nullable=false)
     */
    private $date;

    /**
     * Set date
     *
     * @param DateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Unset date
     * 
     * @return $this
     */
    public function unsetDate()
    {
        $this->date = null;

        return $this;
    }

}
