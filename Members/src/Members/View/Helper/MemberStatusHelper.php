<?php

namespace Members\View\Helper;

use Members\Entity\Member;
use Members\Tool\State;

class MemberStatusHelper
{

    public function table(Member $member, $emptyValue = '-')
    {
        if ((int)$member->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($member);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(Member $member, $emptyValue = '-')
    {
        if ((int)$member->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($member);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(Member $member)
    {
        $status = $member->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Member status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Member status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
