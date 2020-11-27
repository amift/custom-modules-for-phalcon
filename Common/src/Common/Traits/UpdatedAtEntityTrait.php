<?php

namespace Common\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtEntityTrait
{

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default":null})
     */
    private $updatedAt;

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setDefaultUpdatedAt()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

}
