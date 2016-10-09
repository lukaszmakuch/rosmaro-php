<?php

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\State;

class SymbolPrepender extends State
{
    private $symbol;

    public function __construct($symbol)
    {
        $this->symbol = $symbol;
    }

    public function prependSymbols($howMany)
    {
        $newMsg = str_repeat($this->symbol, $howMany) . $this->fetchMessage();
        $newContext = $this->context->copyWith(['msg' => $newMsg]);

        $this->transition(
            ($howMany > 1
                ? "prepended_more_than_1"
                : "prepended_less_than_2"),
            $newContext
        );
    }

    public function fetchMessage()
    {
        return (String)$this->context->msg;
    }
}
