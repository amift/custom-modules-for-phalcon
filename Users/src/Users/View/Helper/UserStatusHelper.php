<?php

namespace Users\View\Helper;

use Users\Entity\User;
use Users\Tool\State;

class UserStatusHelper
{

    public function table(User $user, $emptyValue = '-')
    {
        if ((int)$user->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($user);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(User $user)
    {
        $status = $user->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('User status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('User status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
