<?php

namespace lukaszmakuch\Rosmaro;

class GraphNode
{
    public $arrowsFromIt = [];
    public $isCurrent = false;
    public $id = "";

    public function getArrowFromItWithId($id)
    {
        foreach ($this->arrowsFromIt as $a) {
            if ($a->id == $id) {
                return $a;
            }
        }
    }

    public function getSuccessorOrItselfWithId($id)
    {
        $idsOfVisitedNodes = [];
        return $this->getSuccessorOrItselfWithIdImpl($id, $idsOfVisitedNodes);
    }

    private function getSuccessorOrItselfWithIdImpl($id, &$idsOfVisitedNodes)
    {
        if ($this->id == $id) {
            return $this;
        }

        $idsOfVisitedNodes[] = $this->id;
        foreach ($this->arrowsFromIt as $a) {
            if (in_array($a->head->id, $idsOfVisitedNodes)) {
                continue;
            }

            $foundNode = $a->head->getSuccessorOrItselfWithIdImpl($id, $idsOfVisitedNodes);
            if (!is_null($foundNode)) {
                return $foundNode;
            }
        }

        return null;
    }
}
