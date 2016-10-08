<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\State;

use lukaszmakuch\Rosmaro\Cmd\AddOneSymbol;
use lukaszmakuch\Rosmaro\Cmd\RevertTo;
use lukaszmakuch\Rosmaro\Exception\UnableToHandleCmd;
use lukaszmakuch\Rosmaro\Request\ReversionRequest;
use lukaszmakuch\Rosmaro\Request\TransitionRequest;
use lukaszmakuch\Rosmaro\StateTpl;

class HashAppender extends StateTpl
{
    private $howManyAppended;
    
    public function __construct(&$count)
    {
        $this->howManyAppended = &$count;
    }
    
    /**
     * @return String
     */
    public function getBuiltMessage()
    {
        return $this->context->has("msg") 
            ? $this->context->get("msg") 
            : "";
    }

    protected function handleImpl($cmd)
    {
        switch (get_class($cmd)) {
            case AddOneSymbol::class:
                $this->howManyAppended++;
                return new TransitionRequest("appended", $this->context->getCopyWith([
                    'msg' => $this->getBuiltMessage() . "#"
                ]));
                break;
            case RevertTo::class:
                return new ReversionRequest($cmd->stateInstanceId);
                break;
            default:
                throw new UnableToHandleCmd("unsupported command class");
                
        }
    }
    
    public function cleanUp()
    {
        $this->howManyAppended--;
    }
}
