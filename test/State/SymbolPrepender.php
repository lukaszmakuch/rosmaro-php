<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\State;

class SymbolPrepender extends \lukaszmakuch\Rosmaro\StateTpl
{
    private $symbol;
    
    public function __construct($symbol)
    {
        $this->symbol = $symbol;
    }
    
    /**
     * @return String
     */
    public function fetchMessage()
    {
        return $this->context->has("msg") 
            ? $this->context->get("msg") 
            : "";
    }

    protected function getClassOfSupportedCommands()
    {
        return \lukaszmakuch\Rosmaro\Cmd\PrependSymbols::class;
    }

    protected function handleImpl($cmd)
    {
        /* @var $cmd \lukaszmakuch\Rosmaro\Cmd\PrependSymbols */
        if ($cmd->howMany == 7) {
            return new \lukaszmakuch\Rosmaro\Request\DestructionRequest();
        }
        
        $newMsg = str_repeat($this->symbol, $cmd->howMany) . $this->fetchMessage();
        $newContext = $this->context->getCopyWith(['msg' => $newMsg]);
        return new \lukaszmakuch\Rosmaro\Request\TransitionRequest(
            ($cmd->howMany > 1 ? "prepended_more_than_1" : "prepended_less_than_2"), 
            $newContext
        );
    }
}
