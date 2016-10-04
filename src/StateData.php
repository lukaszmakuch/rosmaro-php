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
    public $id;
    public $stateId;
    public $context;
    
    public function __construct($id, $stateId, $context)
    {
        $this->id = $id;
        $this->stateId = $stateId;
        $this->context = $context;
    }
}