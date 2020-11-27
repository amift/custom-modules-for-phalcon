<?php

namespace Communication\Traits;

use Communication\Tool\NotificationState as State;
use Communication\Tool\TemplateType as Type;

trait LogicAwareTrait
{

    public function isKnownType()
    {
        if (in_array($this->getType(), Type::getTypes())) {
            return true;
        }

        return false;
    }

    public function isTypeEmail()
    {
        if ($this->getType() === Type::TYPE_EMAIL) {
            return true;
        }

        return false;
    }

    public function isKnownState()
    {
        if (in_array($this->getState(), State::getStates())) {
            return true;
        }

        return false;
    }

    public function isStateNew()
    {
        if ($this->getState() === State::STATUS_NEW) {
            return true;
        }

        return false;
    }

    public function isStateSent()
    {
        if ($this->getState() === State::STATUS_SENT) {
            return true;
        }

        return false;
    }

    public function isStateError()
    {
        if ($this->getState() === State::STATUS_ERROR) {
            return true;
        }

        return false;
    }

}