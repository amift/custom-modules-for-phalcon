<?php

namespace Common\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAtEntityTrait
{

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default":null})
     */
    private $createdAt;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDefaultCreatedAt()
    {
        $this->createdAt = new \DateTime('now');
        if (property_exists($this, 'updatedAt')) {
            $this->updatedAt = $this->createdAt;
        }
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

}
