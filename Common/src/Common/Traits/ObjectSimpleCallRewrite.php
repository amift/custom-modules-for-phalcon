<?php

namespace Common\Traits;

trait ObjectSimpleCallRewrite 
{

    public function __call($methodName, $args)
    {
        if (preg_match('~^(set|get)([A-Z])(.*)$~', $methodName, $matches)) {
            $property = strtolower($matches[2]) . $matches[3];
            if (!property_exists($this, $property)) {
                throw new \RuntimeException('Property ' . $property . ' not exists');
            }
            switch ($matches[1]) {
                case 'set' :
                    return $this->set($property, $args[0]);
                case 'get' :
                    return $this->get($property);
            }
        } else {
            if (!method_exists($this, $methodName)) {
                throw new \RuntimeException('Method ' . $methodName . ' not exists');
            }
            $this->$methodName($args[0]);
        }
    }

    public function get($property)
    {
        if (!property_exists($this, $property)) {
            throw new \RuntimeException('Property ' . $property . ' not exists');
        }

        return $this->$property;
    }

    public function set($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \RuntimeException('Property ' . $property . ' not exists');
        }

        $this->$property = $value;

        return $this;
    }

}
