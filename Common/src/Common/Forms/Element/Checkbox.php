<?php

namespace Common\Forms\Element;

use Phalcon\Forms\Element\Check as PhalconCheck;

class Checkbox extends PhalconCheck
{

    /**
     * Renders the element widget returning html
     *
     * @param array|null $attributes Element attributes
     * @return string
     */
    public function render($attributes = null)
    {
        $attrs = array();

        if (!empty($attributes)) {
            foreach ($attributes as $attrName => $attrVal) {
                if (is_numeric($attrName) || in_array($attrName, ['id', 'name', 'value'])) {
                    continue;
                }

                $attrs[] = $attrName .'="'. $attrVal .'"';
            }
        }

        $attrs = ' '. implode(' ', $attrs);

        $id      = $this->getAttribute('id', $this->getName());
        $name    = $this->getName();
        $checked = '';

        if ($this->getValue()) {
            $checked = ' checked="checked"';
        }

        return '
            <input type="hidden" name="'.$name.'" value="0" />
            <input type="checkbox" id="'.$id.'" name="'.$name.'" value="1"'.$attrs.''.$checked.' />';
    }

}
