<?php

namespace Common\Forms\Element;

use Phalcon\Forms\Element\Select as PhalconSelect;

class MuliSelect extends PhalconSelect
{

    /**
     * Renders the element widget returning html
     *
     * @param array|null $attributes Element attributes
     * @return string
     */
    public function render($attributes = null)
    {
        $attr = $this->getAttributes();
        if (isset($attr['id'])) {
            unset($attr['id']);
        }
        if (isset($attr['name'])) {
            unset($attr['name']);
        }
        if (isset($attr['multiple'])) {
            unset($attr['multiple']);
        }
        $attr['multiple'] = 'multiple';
        $id      = $this->getAttribute('id', $this->getName());
        $name    = $this->getName();
        $custAtr = '';
        foreach ($attr as $k => $v) {
            $custAtr.= sprintf(' %s="%s"', $k, $v);
        }
        $selected = is_array($this->getValue()) ? $this->getValue() : [$this->getValue()];
        $options = '';
        foreach ($this->getOptions() as $k => $v) {
            $s = in_array($k, $selected) ? 'selected="selected"' : '';
            $options.= sprintf('<option %s value="%s">%s</option>', $s, $k, $v);
        }

        return '<select id="'.$id.'" name="'.$name.'[]"'.$custAtr.'>'.$options.'</select>';
    }

}
