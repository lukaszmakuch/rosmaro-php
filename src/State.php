<?php

namespace lukaszmakuch\Rosmaro;

abstract class State
{
    protected $context;
    private $rosmaro;

    public function setRosmaro($rosmaro)
    {
        $this->rosmaro = $rosmaro;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function setId($id)
    {
        $this->id = $id;
        $this->intId = crc32($id);
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function transition($arrow, $context)
    {
        $this->rosmaro->transition($arrow, $context);
    }

    public function revert($stateInstanceId)
    {
        $this->rosmaro->revertTo($stateInstanceId);
    }

    public function remove()
    {
        $this->rosmaro->remove();
    }

    public function cleanUp() {}
}
