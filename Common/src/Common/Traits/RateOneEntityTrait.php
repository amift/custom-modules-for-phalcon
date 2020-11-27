<?php

namespace Common\Traits;

trait RateOneEntityTrait
{

    /**
     * @var smallint
     * @ORM\Column(name="rate", type="smallint", nullable=false, options={"unsigned":false})
     */
    private $rate;

    /**
     * Set rate
     * 
     * @param smallint $rate
     * @return $this
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     * 
     * @return boolean 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Check if rate is positive
     * 
     * @return boolean
     */
    public function isPositiveRate()
    {
        return $this->rate > 0 ? true : false;
    }

    /**
     * Check if rate is negative
     * 
     * @return boolean
     */
    public function isNegativeRate()
    {
        return $this->rate > 0 ? true : false;
    }

}
