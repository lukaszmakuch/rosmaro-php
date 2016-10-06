<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\State;

class HashAppender extends \lukaszmakuch\Rosmaro\StateTpl
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

    protected function getClassOfSupportedCommands()
    {
        return \lukaszmakuch\Rosmaro\Cmd\AddOneSymbol::class;
    }

    protected function handleImpl($cmd)
    {
        $this->howManyAppended++;
        return new \lukaszmakuch\Rosmaro\Request\TransitionRequest("appended", $this->context->getCopyWith([
            'msg' => $this->getBuiltMessage() . "#"
        ]));
    }
    
    public function cleanUp()
    {
        $this->howManyAppended--;
    }
}
