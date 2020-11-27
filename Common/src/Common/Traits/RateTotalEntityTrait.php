<?php

namespace Common\Traits;

trait RateTotalEntityTrait
{

    /**
     * @var smallint
     * @ORM\Column(name="rate_plus", type="smallint", nullable=false, options={"unsigned":true,"default":0})
     */
    private $ratePlus;

    /**
     * @var smallint
     * @ORM\Column(name="rate_minus", type="smallint", nullable=false, options={"unsigned":true,"default":0})
     */
    private $rateMinus;

    /**
     * @var smallint
     * @ORM\Column(name="rate_avg", type="smallint", nullable=false, options={"unsigned":false,"default":0})
     */
    private $rateAvg;

    /**
     * Set ratePlus
     * 
     * @param smallint $ratePlus
     * @return $this
     */
    public function setRatePlus($ratePlus)
    {
        $this->ratePlus = $ratePlus;

        return $this;
    }

    /**
     * Get ratePlus
     * 
     * @return smallint 
     */
    public function getRatePlus()
    {
        return $this->ratePlus;
    }

    /**
     * Set rateMinus
     * 
     * @param smallint $rateMinus
     * @return $this
     */
    public function setRateMinus($rateMinus)
    {
        $this->rateMinus = $rateMinus;

        return $this;
    }

    /**
     * Get rateMinus
     * 
     * @return smallint 
     */
    public function getRateMinus()
    {
        return $this->rateMinus;
    }

    /**
     * Set rateAvg
     * 
     * @param smallint $rateAvg
     * @return $this
     */
    public function setRateAvg($rateAvg)
    {
        $this->rateAvg = $rateAvg;

        return $this;
    }

    /**
     * Get rateAvg
     * 
     * @return smallint 
     */
    public function getRateAvg()
    {
        return $this->rateAvg;
    }

    /**
     * Check if rateAvg is positive
     * 
     * @return boolean
     */
    public function isPositiveRateAvg()
    {
        return $this->rateAvg > 0 ? true : false;
    }

    /**
     * Check if rateAvg is negative
     * 
     * @return boolean
     */
    public function isNegativeRateAvg()
    {
        return $this->rateAvg < 0 ? true : false;
    }

}
