<?php

namespace Common\Traits;

trait BlockedEntityTrait
{

    /**
     * @var boolean
     * @ORM\Column(name="`blocked`", type="boolean", nullable=false, options={"default":false})
     */
    private $blocked = false;

    /**
     * Set blocked
     * 
     * @param boolean $blocked
     * @return $this
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked
     * 
     * @return boolean 
     */
    public function getBlocked()
    {
        return $this->blocked ? 1 : 0;
    }

    /**
     * Check if is blocked
     * 
     * @return boolean 
     */
    public function isBlocked()
    {
        return $this->blocked === true ? true : false;
    }

    /**
     * Check if is not blocked
     * 
     * @return boolean 
     */
    public function notBlocked()
    {
        return $this->blocked !== true ? true : false;
    }

}
