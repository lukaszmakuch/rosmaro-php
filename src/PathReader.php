<?php

namespace lukaszmakuch\Rosmaro;

class PathReader
{
    private $preferredArrows;

    public function __construct($preferredArrows = [])
    {
        $this->preferredArrows = $preferredArrows;
    }

    public function getNodesOf($rosmaro)
    {
        $history = $rosmaro->history;
        $recentHistoryEntry = end($history);
        $visitedNodes = array_map(function ($historyEntry) use ($recentHistoryEntry) {
            return [
                'id' => $historyEntry['id'],
                'type' => $historyEntry['type'],
                'visited' => true,
                'current' => $historyEntry['id'] === $recentHistoryEntry['id']
            ];
        }, $history);
        $currentGraphNode = $rosmaro->graph->getSuccessorOrItselfWithId($recentHistoryEntry['type']);
        return array_merge(
            $visitedNodes,
            $this->getPreferredPathFrom($currentGraphNode)
        );
    }

    private function getPreferredPathFrom($graphNode)
    {
        $path = [];
        $currentGraphNode = $graphNode;
        while (true) {
            $newPathNode = [
                'id' => null,
                'type' => $currentGraphNode->id,
                'visited' => $currentGraphNode->isCurrent,
                'current' => $currentGraphNode->isCurrent,
            ];
            if ($this->inPath($newPathNode, $path)) {
                break;
            }

            $path[] = $newPathNode;
            if (empty($currentGraphNode->arrowsFromIt)) {
                break;
            }

            $currentGraphNode = $this->getPreferredArrowFrom($currentGraphNode)->head;
        }

        return array_slice($path, 1);
    }

    private function getPreferredArrowFrom($graphNode)
    {
        return isset($this->preferredArrows[$graphNode->id])
            ? $graphNode->getArrowFromItWithId($this->preferredArrows[$graphNode->id])
            : $graphNode->arrowsFromIt[0];
    }

    private function inPath($pathNode, $path)
    {
        foreach ($path as $alreadyWithinPath) {
            if ($alreadyWithinPath == $pathNode) {
                return true;
            }
        }

        return false;
    }
}
