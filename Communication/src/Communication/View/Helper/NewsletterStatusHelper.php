<?php

namespace Communication\View\Helper;

use Communication\Entity\Newsletter;
use Communication\Tool\NewsletterState as State;

class NewsletterStatusHelper
{
    public function table(Newsletter $newsletter, $emptyValue = '-')
    {
        if ((int)$newsletter->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($newsletter);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(Newsletter $newsletter, $emptyValue = '-')
    {
        if ((int)$newsletter->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($newsletter);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(Newsletter $newsletter)
    {
        $status = $newsletter->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Newsletter status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Newsletter status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }
}