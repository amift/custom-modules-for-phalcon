<?php

namespace Statistics\Traits;

trait StatsDateMonthlyEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="`year`", type="string", length=4, nullable=true)
     */
    private $year;

    /**
     * @var string
     * @ORM\Column(name="`month`", type="string", length=2, nullable=true)
     */
    private $month;

    /**
     * Set year
     *
     * @param string $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param string $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

}
