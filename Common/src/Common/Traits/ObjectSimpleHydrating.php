<?php

namespace Common\Traits;

trait ObjectSimpleHydrating 
{

    /**
     * Assign array data to object properties.
     * 
     * @access public
     * @param array $data
     * @return void
     */
    public function exchangeArray(array $data = [])
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }
    }

    /**
     * Get object properties as array data
     * 
     * @access public
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}
