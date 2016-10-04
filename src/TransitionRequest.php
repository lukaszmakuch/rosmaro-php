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
    /**
     * @var String
     */
    public $edge;
    
    /**
     * @var Context
     */
    public $context;
    
    public function __construct($edge, Context $context)
    {
        $this->edge = $edge;
        $this->context = $context;
    }
}