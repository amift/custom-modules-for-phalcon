<?php

namespace Polls\View\Helper;

use Polls\Entity\Poll;
use Polls\Tool\State;

class PollStatusHelper
{

    public function table(Poll $object, $emptyValue = '-')
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

    public function big(Poll $object, $emptyValue = '-')
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

    protected function getData(Poll $object)
    {
        $status = $object->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Poll status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Poll status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}

