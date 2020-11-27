<?php

namespace Members\View\Helper;

use Members\Entity\Withdraws;
use Members\Tool\WithdrawState;

class WithdrawStatusHelper
{

    public function table(Withdraws $object, $emptyValue = '-')
    {
        if ((int)$object->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($object);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(Withdraws $object, $emptyValue = '-')
    {
        if ((int)$object->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($object);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(Withdraws $object)
    {
        $status = $object->getState();
        $styles = WithdrawState::getStyles();
        $labels = WithdrawState::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Withdraw status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Withdraw status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}


