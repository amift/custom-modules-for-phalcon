<?php

namespace Common\Traits;

use Doctrine\ORM\Mapping as ORM;

trait StateChangedAtEntityTrait
{

    /**
     * @var \DateTime
     * @ORM\Column(name="state_changed_at", type="datetime", nullable=true, options={"default":null})
     */
    private $stateChangedAt;

    /**
     * Set stateChangedAt
     *
     * @param \DateTime $stateChangedAt
     * @return $this
     */
    public function setStateChangedAt($stateChangedAt)
    {
        $this->stateChangedAt = $stateChangedAt;

        return $this;
    }

    /**
     * Get stateChangedAt
     *
     * @return \DateTime
     */
    public function getStateChangedAt()
    {
        return $this->stateChangedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultStateChangedAt()
    {
        if (property_exists($this, 'createdAt')) {
            $this->stateChangedAt = $this->createdAt;
        } else {
            $this->stateChangedAt = new \DateTime('now');
        }
    }

}
