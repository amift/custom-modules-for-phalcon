<?php

namespace System\View\Helper;

use System\Entity\CronJob;
use System\Tool\CronJobState as State;

class CronJobStatusHelper
{

    public function table(CronJob $cronJob, $emptyValue = '-')
    {
        if ((int)$cronJob->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($cronJob);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(CronJob $cronJob, $emptyValue = '-')
    {
        if ((int)$cronJob->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($cronJob);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(CronJob $cronJob)
    {
        $status = $cronJob->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('CronJob status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('CronJob status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}

