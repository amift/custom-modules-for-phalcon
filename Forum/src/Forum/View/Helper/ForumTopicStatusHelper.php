<?php

namespace Forum\View\Helper;

use Forum\Entity\ForumTopic;
use Forum\Tool\ForumTopicState;

class ForumTopicStatusHelper
{

    public function table(ForumTopic $topic, $emptyValue = '-')
    {
        if ((int)$topic->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($topic);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(ForumTopic $topic, $emptyValue = '-')
    {
        if ((int)$topic->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($topic);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(ForumTopic $topic)
    {
        $status = $topic->getState();
        $styles = ForumTopicState::getStyles();
        $labels = ForumTopicState::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Topic status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Topic status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
