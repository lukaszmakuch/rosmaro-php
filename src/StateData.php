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
    /**
     * @var String
     */
    public $id;
    
    /**
     * @var String
     */
    public $stateId;
    
    /**
     * @var Context 
     */
    public $context;
    
    public function __construct($id, $stateId, Context $context)
    {
        $this->id = $id;
        $this->stateId = $stateId;
        $this->context = $context;
    }
}