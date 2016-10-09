<?php

namespace lukaszmakuch\Rosmaro;

class GraphNode
{
    public $arrowsFromIt = [];
    public $isCurrent = false;
    public $id = "";

    public function getArrowFromItWith($id)
    {
        foreach ($this->arrowsFromIt as $a) {
            if ($a->id == $id) {
                return $a;
            }
        }
    }

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
