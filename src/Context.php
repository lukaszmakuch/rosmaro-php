<?php

namespace lukaszmakuch\Rosmaro;

class Context implements \Serializable
{
    private $values;

    public function __construct($values = [])
    {
        $this->values = $values;
    }

    public function __get($name)
    {
        return isset($this->values[$name])
            ? $this->values[$name]
            : null;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->values);
    }

    public function copyWith($differentValues)
    {
        return new Context(array_merge($this->values, $differentValues));
    }

    public function serialize()
    {
        return serialize($this->values);
    }

    public function unserialize($serialized)
    {
        $this->values = unserialize($serialized);
    }
}
