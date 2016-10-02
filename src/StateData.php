<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

class StateData
{
    private $id;
    private $stateId;
    private $context;
    
    public function __construct($id, $stateId, $context)
    {
        $this->id = $id;
        $this->stateId = $stateId;
        $this->context = $context;
    }
    
    /**
     * @return String
     */
    public function getid()
    {
        return $this->id;
    }
    
    /**
     * @return String
     */
    public function getStateId()
    {
        return $this->stateId;
    }
    
    /**
     * @return Context
     */
    public function getStateContext()
    {
        return $this->context;
    }
}