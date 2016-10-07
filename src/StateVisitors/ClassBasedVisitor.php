<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateVisitors;

use lukaszmakuch\Rosmaro\Exception\VisitationFailed;
use lukaszmakuch\Rosmaro\State;
use lukaszmakuch\Rosmaro\StateVisitor;

class ClassBasedVisitor implements StateVisitor
{
    private $visitorsByClassOfSupportedStates = [];
    
    /**
     * @param StateVisitor[] $actualVisitorsByClassOfSupportedStates like
     * <pre>
     * [
     *     SomeClass::class => new SomeClassVisitor(),
     * ]
     * </pre>
     */
    public function __construct(array $actualVisitorsByClassOfSupportedStates)
    {
        $this->visitorsByClassOfSupportedStates = $actualVisitorsByClassOfSupportedStates;
    }
    
    public function visit(State $s)
    {
        $visitedStateClass = get_class($s);
        if (!isset($this->visitorsByClassOfSupportedStates[$visitedStateClass])) {
            throw new VisitationFailed("unsupported class of the visited state");
        }
        
        return $s->accept($this->visitorsByClassOfSupportedStates[$visitedStateClass]);
    }
}