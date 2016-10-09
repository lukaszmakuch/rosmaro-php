<?php

namespace lukaszmakuch\Rosmaro;

abstract class State
{
    protected $context;
    private $rosmaro;

    public function setRosmaro($rosmaro)
    {
        $this->rosmaro = $rosmaro;
        return $this;
    }

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        $this->idHash = sha1($id);
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function transition($arrow, $context)
    {
        $this->rosmaro->transition($arrow, $context);
    }

    public function revertTo($stateInstanceId)
    {
        $this->rosmaro->revertTo($stateInstanceId);
    }

    public function remove()
    {
        $this->rosmaro->remove();
    }

    public function cleanUp() {}
}
