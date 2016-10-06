<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateVisitors;

use lukaszmakuch\Rosmaro\State;
use lukaszmakuch\Rosmaro\StateVisitor;

class CallableBasedVisitor implements StateVisitor
{
    private $callableVisitor;
    
    public function __construct(callable $visitor)
    {
        $this->callableVisitor = $visitor;
    }
    
    public function visit(State $s)
    {
        $v = $this->callableVisitor;
        return $v($s);
    }
}