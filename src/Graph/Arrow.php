<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class Arrow
{
    /**
     * @var Node 
     */
    public $head;
    
    /**
     * @var Node 
     */
    public $tail;
    
    /**
     * @var String
     */
    public $id = "";
}

