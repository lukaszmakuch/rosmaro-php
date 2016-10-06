<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\PathPresenter;

use lukaszmakuch\Rosmaro\Graph\Node;
use lukaszmakuch\Rosmaro\Rosmaro;
use lukaszmakuch\Rosmaro\State;

class PathPresenter
{
    private $preferredArrows;
    
    public function __construct(array $preferredArrows = [])
    {
        $this->preferredArrows = $preferredArrows;
    }
    
    /**
     * @param Rosmaro $r
     * @return FlatNode[]
     */
    public function getNodesOf(Rosmaro $r)
    {
        $allStates = $r->getAllStates();
        /* @var $currentState State */
        $currentState = end($allStates);
        $visitedNodes = array_map(function (State $s) {
            return new FlatNode($s->getId(), true, false);
        }, array_slice($allStates, 0, -1));
            
        $currentGraphNode = $r->getGraph()->getSuccessorOrItselfWith($currentState->getId());
        return array_merge($visitedNodes, $this->getPreferredPathFrom($currentGraphNode, true));
    }
    
    /**
     * @param Node $node
     * @return FlatNode[]
     */
    private function getPreferredPathFrom(Node $node, $isCurrentNode)
    {
        $nodeAsFlatNode = new FlatNode($node->id, $isCurrentNode, $isCurrentNode);
        if (empty($node->arrowsFromIt)) {
            return [$nodeAsFlatNode];
        }
        
        $preferredArrow = isset($this->preferredArrows[$node->id])
            ? $node->getArrowFromItWith($this->preferredArrows[$node->id])
            : $node->arrowsFromIt[0];
        
        return array_merge(
            [$nodeAsFlatNode],
            $this->getPreferredPathFrom($preferredArrow->head, false)
        );
    }
}