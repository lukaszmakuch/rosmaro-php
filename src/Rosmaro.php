<?php

namespace lukaszmakuch\Rosmaro;

class Rosmaro
{
    private $id;
    private $initialState;
    private $storage;
    private $transitions;
    private $statePrototypes;
    private $initialStateData;

    public function __construct(
        $id,
        $initialState,
        $transtions,
        $statePrototypes,
        $storage
    ) {
        $this->id = $id;
        $this->storage = $storage;
        $this->transitions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->initialState = $initialState;
        $this->initialStateData = [
            'id' => uniqid(),
            'type' => $this->initialState,
            'context' => new Context(),
        ];
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getCurrentState(), $name], $arguments);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'history':
                return array_map(function ($state) {
                    return [
                        'id' => $state->id,
                        'type' => $state->type,
                    ];
                }, $this->getAllStates());
            case 'graph':
                return $this->getGraph();
            default:
                return $this->getCurrentState()->$name;
        }
    }

    private function getGraph()
    {
        //create all nodes
        $nodeById = [];
        $idsOfNodes = array_keys($this->statePrototypes);
        foreach ($idsOfNodes as $nodeId) {
            $nodeById[$nodeId] = new GraphNode();
            $nodeById[$nodeId]->id = $nodeId;
            $nodeById[$nodeId]->isCurrent = ($nodeId == $this->getCurrentStateType());
        }

        //add arrows
        foreach ($this->transitions as $tailNodeId => $arrowsData) {
            foreach ($arrowsData as $arrowId => $headNodeId) {
                $arrow = new \stdClass();
                $arrow->id = $arrowId;
                $arrow->tail = $nodeById[$tailNodeId];
                $arrow->head = $nodeById[$headNodeId];
                $nodeById[$tailNodeId]->arrowsFromIt[] = $arrow;
            }
        }

        //return the root node
        return $nodeById[$this->initialState];
    }

    public function transition($arrow, $context)
    {
        $this->storage->storeFor($this->id, [
            'id' => uniqid(),
            'type' => $this->transitions[$this->getCurrentStateType()][$arrow],
            'context' => $context
        ]);
    }


    public function revertTo($stateId)
    {
        $abandonedStates = [];
        $isAbandoned = false;
        foreach ($this->getAllStates() as $possiblyAbandoned) {
            if ($possiblyAbandoned->id == $stateId) {
                $isAbandoned = true;
            }
            if ($isAbandoned) {
                $abandonedStates[] = $possiblyAbandoned;
            }
        }

        $this->storage->revertFor($this->id, $stateId);
        foreach ($abandonedStates as $toClean) {
            $toClean->cleanUp();
        }
    }

    public function revertToPreviousState()
    {
        $allStates = $this->getAllStates();
        if (count($allStates) < 2) {
            throw new Error("no previous state");
        }

        $previousState = array_slice($allStates, -2, 1)[0];
        $this->revertTo($previousState->id);
    }

    public function cleanUp()
    {
        foreach ($this->getAllStates() as $s) {
            $s->cleanUp();
        }
    }

    public function remove()
    {
        $this->cleanUp();
        $this->storage->removeAllDataFor($this->id);
    }


    private function getAllStates()
    {
        return array_values(array_map(function ($stateData) {
            return $this->buildState(
                $stateData['id'],
                $stateData['type'],
                $stateData['context']
            );
        }, $this->storage->getAllFor(
            $this->id,
            $this->initialStateData
        )));
    }

    private function getCurrentState()
    {
        $stateData = $this->storage->getRecentFor(
            $this->id,
            $this->initialStateData
        );
        return $this->buildState(
            $stateData['id'],
            $stateData['type'],
            $stateData['context']
        );
    }

    private function getCurrentStateType()
    {
        return $this->storage->getRecentFor(
            $this->id,
            $this->initialStateData
        )['type'];
    }

    private function buildState($stateInstanceId, $stateId, Context $context)
    {
        $s = clone $this->statePrototypes[$stateId];
        $s->setContext($context);
        $s->setType($stateId);
        $s->setId($stateInstanceId);
        $s->setRosmaro($this);
        return $s;
    }
}
