<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

class TransitionRequest
{
    private $edge;
    private $context;
    
    public function __construct($edge, Context $context)
    {
        $this->edge = $edge;
        $this->context = $context;
    }
    
    /**
     * @return String
     */
    public function getEdge()
    {
        return $this->edge;
    }
    
    /**
     * @return Context
     */
    public function getStateContext()
    {
        return $this->context;
    }
}