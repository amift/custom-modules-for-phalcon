<?php

namespace Members\Traits;

use Members\Tool\State;

trait LogicAwareTrait
{

    /**
     * Check if is confirmed
     * 
     * @return boolean 
     */
    public function isConfirmed()
    {
        return $this->confirmed === true ? true : false;
    }

    /**
     * Check if is not confirmed
     * 
     * @return boolean 
     */
    public function isNotConfirmed()
    {
        return $this->confirmed === true ? false : true;
    }

    /**
     * Check if is active
     * 
     * @return boolean 
     */
    public function isActive()
    {
        return $this->state === (int)State::STATE_ACTIVE ? true : false;
    }

    /**
     * Check if is inactive
     * 
     * @return boolean 
     */
    public function isInactive()
    {
        return $this->state === (int)State::STATE_INACTIVE ? true : false;
    }

    /**
     * Check if is blocked
     * 
     * @return boolean 
     */
    public function isBlocked()
    {
        return $this->state === (int)State::STATE_BLOCKED ? true : false;
    }

    /**
     * Check if is banned
     * 
     * @return boolean 
     */
    public function isBanned()
    {
        return $this->state === (int)State::STATE_BANNED ? true : false;
    }

    /**
     * Alias for "getConfirmed"
     * 
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->confirmed;
    }

}
