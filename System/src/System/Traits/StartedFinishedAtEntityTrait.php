<?php

namespace System\Traits;

use Doctrine\ORM\Mapping as ORM;

trait StartedFinishedAtEntityTrait
{

    /**
     * @var \DateTime
     * @ORM\Column(name="launched_at", type="datetime", nullable=true, options={"default":null})
     */
    private $launchedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="finished_at", type="datetime", nullable=true, options={"default":null})
     */
    private $finishedAt;

    /**
     * Set launchedAt
     * 
     * @param \DateTime  $launchedAt
     * @return $this
     */
    public function setLaunchedAt($launchedAt)
    {
        $this->launchedAt = $launchedAt;

        return $this;
    }

    /**
     * Get launchedAt
     * 
     * @return \DateTime 
     */
    public function getLaunchedAt()
    {
        return $this->launchedAt;
    }

    /**
     * Set finishedAt
     * 
     * @param \DateTime  $finishedAt
     * @return $this
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * Get finishedAt
     * 
     * @return \DateTime 
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

}
