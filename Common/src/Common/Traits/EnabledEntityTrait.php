<?php

namespace Common\Traits;

trait EnabledEntityTrait
{

    /**
     * @var boolean
     * @ORM\Column(name="`enabled`", type="boolean", nullable=false, options={"default":true})
     */
    private $enabled = true;

    /**
     * Set enabled
     * 
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     * 
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled ? 1 : 0;
    }

    /**
     * Check if is enabled
     * 
     * @return boolean 
     */
    public function isEnabled()
    {
        return $this->enabled === true ? true : false;
    }

    /**
     * Check if is disabled
     * 
     * @return boolean 
     */
    public function isDisabled()
    {
        return $this->enabled === true ? false : true;
    }

}
