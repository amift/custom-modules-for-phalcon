<?php

namespace Common\View\Tag;

use Common\Tool\Enable;

class EnableStatusHelper
{

    public function table($object, $labels = null, $styles = null)
    {
        if (!method_exists($object, 'getEnabled')) {
            throw new \InvalidArgumentException(sprintf('Object "%s" do not have enabled trait or property used', (string)$object));
        }

        list($title, $class) = $this->getData($object, $labels, $styles);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big($object, $labels = null, $styles = null)
    {
        if (!method_exists($object, 'getEnabled')) {
            throw new \InvalidArgumentException(sprintf('Object "%s" do not have enabled trait or property used', (string)$object));
        }

        list($title, $class) = $this->getData($object, $labels, $styles);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function tableByCustomValue($status, $labels = null, $styles = null)
    {
        list($title, $class) = $this->getDataByCustomValue($status, $labels, $styles);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData($object, $labels = null, $styles = null)
    {
        $status = $object->getEnabled();
        $styles = is_null($styles) ? Enable::getStyles() : $styles;
        $labels = is_null($labels) ? Enable::getLabels() : $labels;

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Enabled status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Enabled status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

    protected function getDataByCustomValue($status, $labels = null, $styles = null)
    {
        $styles = is_null($styles) ? Enable::getStyles() : $styles;
        $labels = is_null($labels) ? Enable::getLabels() : $labels;

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Custom value "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Custom value "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
