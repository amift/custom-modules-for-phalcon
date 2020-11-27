<?php

namespace Common\Traits;

trait OrderingEntityTrait
{

    /**
     * @var integer
     * @ORM\Column(name="ordering", type="integer", nullable=false, options={"unsigned":true})
     */
    private $ordering;

    /**
     * Set ordering
     * 
     * @param integer $ordering
     * @return $this
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get ordering
     * 
     * @return integer 
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

}
