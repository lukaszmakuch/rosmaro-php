<?php

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\State;

class HashAppender extends State
{
    private $howManyAppended;

    public function __construct(&$count)
    {
        $this->howManyAppended = &$count;
    }

    public function addOneSymbol()
    {
        $this->howManyAppended++;
        $this->transition("appended", $this->context->copyWith([
            'msg' => $this->getBuiltMessage() . "#"
        ]));
    }

    public function getBuiltMessage()
    {
        return (String)$this->context->msg;
    }

    public function cleanUp()
    {
        $this->howManyAppended--;
    }
}
