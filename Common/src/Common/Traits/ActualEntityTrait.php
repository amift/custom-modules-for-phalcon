<?php

namespace Common\Traits;

trait ActualEntityTrait
{

    /**
     * @var boolean
     * @ORM\Column(name="`actual`", type="boolean", nullable=false, options={"default":false})
     */
    private $actual = false;

    /**
     * Set actual
     * 
     * @param boolean $actual
     * @return $this
     */
    public function setActual($actual)
    {
        $this->actual = $actual;

        return $this;
    }

    /**
     * Get actual
     * 
     * @return boolean 
     */
    public function getActual()
    {
        return $this->actual ? 1 : 0;
    }

    /**
     * Check if is actual
     * 
     * @return boolean 
     */
    public function isActual()
    {
        return $this->actual === true ? true : false;
    }

    /**
     * Check if is not actual
     * 
     * @return boolean 
     */
    public function isNotActual()
    {
        return $this->actual === true ? false : true;
    }

}
