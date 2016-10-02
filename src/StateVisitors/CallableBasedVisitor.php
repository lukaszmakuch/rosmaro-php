<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateVisitors;

class CallableBasedVisitor implements \lukaszmakuch\Rosmaro\StateVisitor
{
    private $callableVisitor;
    
    public function __construct(callable $visitor)
    {
        $this->callableVisitor = $visitor;
    }
    
    public function visit(\lukaszmakuch\Rosmaro\State $s)
    {
        $v = $this->callableVisitor;
        return $v($s);
    }
}