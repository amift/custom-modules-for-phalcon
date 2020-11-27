<?php

namespace Common\Traits;

trait UncheckedEntityTrait
{

    /**
     * @var boolean
     * @ORM\Column(name="`unchecked`", type="boolean", nullable=false, options={"default":true})
     */
    private $unchecked = true;

    /**
     * Set unchecked
     * 
     * @param boolean $unchecked
     * @return $this
     */
    public function setUnchecked($unchecked)
    {
        $this->unchecked = $unchecked;

        return $this;
    }

    /**
     * Get unchecked
     * 
     * @return boolean 
     */
    public function getUnchecked()
    {
        return $this->unchecked ? 1 : 0;
    }

    /**
     * Check if is not checked
     * 
     * @return boolean 
     */
    public function isUnchecked()
    {
        return $this->unchecked === true ? true : false;
    }

    /**
     * Check if is checked
     * 
     * @return boolean 
     */
    public function isChecked()
    {
        return $this->unchecked !== true ? true : false;
    }

}
