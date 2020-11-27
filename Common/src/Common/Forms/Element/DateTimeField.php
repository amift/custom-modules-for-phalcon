<?php

namespace Common\Forms\Element;

use Phalcon\Forms\Element\Text;

class DateTimeField extends Text
{

    private $_fieldId;

    private $_fieldFormat = 'DD/MM/YYYY HH:mm:ss';

    public function setFieldId($value)
    {
        $this->_fieldId = $value;
    }

    public function getFieldId()
    {
        return $this->_fieldId;
    }

    public function setFieldFormat($value)
    {
        $this->_fieldFormat = $value;
    }

    public function getFieldFormat()
    {
        return $this->_fieldFormat;
    }

}
