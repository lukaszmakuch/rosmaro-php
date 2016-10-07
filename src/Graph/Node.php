<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class Node
{
    /**
     * @var Arrow[] 
     */
    public $arrowsFromIt = [];
    
    /**
     * @var boolean
     */
    public $isCurrent = false;

    /**
     * @var String
     */
    public $id = "";
    
    /**
     * @param String $id
     * @return Arrow|null null if not found
     */
    public function getArrowFromItWith($id)
    {
        foreach ($this->arrowsFromIt as $a) {
            if ($a->id == $id) {
                return $a;
            }
        }
    }
    
    /**
     * @param String $id
     * @return Node|null null if not found
     */
    public function getSuccessorOrItselfWith($id)
    {
        $idsOfVisitedNodes = [];
        return $this->getSuccessorOrItselfWithImpl($id, $idsOfVisitedNodes);
    }
    
    private function getSuccessorOrItselfWithImpl($id, array &$idsOfVisitedNodes)
    {
        if ($this->id == $id) {
            return $this;
        }
        
        $idsOfVisitedNodes[] = $this->id;
        foreach ($this->arrowsFromIt as $a) {
            if (in_array($a->head->id, $idsOfVisitedNodes)) {
                continue;
            }
            
            $foundNode = $a->head->getSuccessorOrItselfWithImpl($id, $idsOfVisitedNodes);
            if (!is_null($foundNode)) {
                return $foundNode;
            }
        }
        
        return null;
    }
}

