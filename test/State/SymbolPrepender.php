<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\State;

use lukaszmakuch\Rosmaro\Cmd\PrependSymbols;
use lukaszmakuch\Rosmaro\Exception\UnableToHandleCmd;
use lukaszmakuch\Rosmaro\Request\DestructionRequest;
use lukaszmakuch\Rosmaro\Request\TransitionRequest;
use lukaszmakuch\Rosmaro\StateTpl;

class SymbolPrepender extends StateTpl
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

    protected function throwExceptionIfInvalidContext()
    {
        if (strlen($this->fetchMessage()) >= 50) {
            throw new UnableToHandleCmd("too long message");
        }
    }

    protected function handleImpl($cmd)
    {
        switch (get_class($cmd)) {
            case PrependSymbols::class:
                if ($cmd->howMany == 99) {
                    throw new UnableToHandleCmd("99 is a bad number");
                }
                
                if ($cmd->howMany == 7) {
                    return new DestructionRequest();
                }
                
                $newMsg = str_repeat($this->symbol, $cmd->howMany) . $this->fetchMessage();
                $newContext = $this->context->getCopyWith(['msg' => $newMsg]);
                return new TransitionRequest(
                    ($cmd->howMany > 1 ? "prepended_more_than_1" : "prepended_less_than_2"), 
                    $newContext
                );
                break;
            case \lukaszmakuch\Rosmaro\Cmd\RevertTo::class:
                return new \lukaszmakuch\Rosmaro\Request\ReversionRequest($cmd->stateInstanceId);
                break;
            default:
                throw new UnableToHandleCmd("unsupported command class");
                
        }
    }
}
