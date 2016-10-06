<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Request;

use lukaszmakuch\Rosmaro\Context;

/**
 * Returned by a state when a transition should take place.
 * 
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 */
class TransitionRequest
{
    /**
     * @var String
     */
    public $edge;
    
    /**
     * @var Context2
     */
    public $context;
    
    /**
     * @param String $edge id of the arrow that should be followed
     * @param Context $context for the new state (head of the given arrow)
     */
    public function __construct($edge, Context $context)
    {
        $this->edge = $edge;
        $this->context = $context;
    }
}