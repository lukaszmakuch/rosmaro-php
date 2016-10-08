<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\PathPresenter;

use lukaszmakuch\Rosmaro\Graph\Arrow;
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
     * @return PathNode[]
     */
    public function getNodesOf(Rosmaro $r)
    {
        $allStates = $r->getAllStates();
        $currentState = end($allStates);
        $visitedNodes = array_map(function (State $s) use ($currentState) {
            return new PathNode($s->getInstanceId(), $s->getStateId(), true, $s === $currentState);
        }, $allStates);
        $currentGraphNode = $r->getGraph()->getSuccessorOrItselfWith($currentState->getStateId());
        return array_merge($visitedNodes, $this->getPreferredPathFrom($currentGraphNode));
    }
    
    /**
     * @param Node $node
     * @return PathNode[] 
     */
    private function getPreferredPathFrom(Node $node)
    {
        $path = [];
        $currentNode = $node;
        while (true) {
            $newPathNode = $this->mapToPathNode($currentNode);
            if ($this->inPath($newPathNode, $path)) {
                break;
            }
            
            $path[] = $newPathNode;
            if (empty($currentNode->arrowsFromIt)) {
                break;
            }
            
            $currentNode = $this->getPreferredArrowFrom($currentNode)->head;
        }
        
        return array_slice($path, 1);
    }
    
    private function mapToPathNode(Node $n)
    {
        return new PathNode(null, $n->id, $n->isCurrent, $n->isCurrent);
    }
    
    /**
     * @param Node $n
     * @return Arrow
     */
    private function getPreferredArrowFrom(Node $n)
    {
        return isset($this->preferredArrows[$n->id])
            ? $n->getArrowFromItWith($this->preferredArrows[$n->id])
            : $n->arrowsFromIt[0];
    }
    
    /**
     * @param PathNode $n
     * @param PathNode[] $path
     * @return boolean
     */
    private function inPath(PathNode $n, array $path)
    {
        foreach ($path as $alreadyWithinPath) {
            if ($alreadyWithinPath->equals($n)) {
                return true;
            }
        }
            
        return false;
    }
}