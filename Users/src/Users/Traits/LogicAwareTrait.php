<?php

namespace Users\Traits;

use Users\Tool\State;

trait LogicAwareTrait
{

    public function isActive()
    {
        return $this->state === State::STATE_ACTIVE ? true : false;
    }

    public function isInactive()
    {
        return $this->state === State::STATE_INACTIVE ? true : false;
    }

    public function isBlocked()
    {
        return $this->state === State::STATE_BLOCKED ? true : false;
    }

}
