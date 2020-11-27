<?php

namespace Communication\View\Helper;

use Communication\Entity\Notification;
use Communication\Tool\NotificationState as State;

class NotificationStatusHelper
{

    public function table(Notification $notification, $emptyValue = '-')
    {
        if ((int)$notification->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($notification);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(Notification $notification, $emptyValue = '-')
    {
        if ((int)$notification->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($notification);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(Notification $notification)
    {
        $status = $notification->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Notification status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Notification status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
