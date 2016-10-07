<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Graph\Arrow;
use lukaszmakuch\Rosmaro\Graph\Node;
use lukaszmakuch\Rosmaro\Request\DestructionRequest;
use lukaszmakuch\Rosmaro\Request\TransitionRequest;

class Rosmaro implements State
{
    private $id;
    private $initialStateId;
    private $stateDataStorage;
    private $transitions;
    private $statePrototypes;
    private $initialStateData;
    
    public function __construct(
        $id,
        $initialStateId,
        array $transtions,
        array $statePrototypes,
        StateDataStorage $stateDataStorage
    ) {
        $this->id = $id;
        $this->stateDataStorage = $stateDataStorage;
        $this->transitions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->initialStateId = $initialStateId;
        $this->initialStateData = new StateData(
            uniqid(), 
            $this->initialStateId, 
            new Context()
        );
    }
    
    /**
     * @return Node
     */
    public function getGraph()
    {
        //create all nodes
        $nodeById = [];
        $idsOfNodes = array_keys($this->statePrototypes);
        foreach ($idsOfNodes as $nodeId) {
            $nodeById[$nodeId] = new Node();
            $nodeById[$nodeId]->id = $nodeId;
            $nodeById[$nodeId]->isCurrent = ($nodeId == $this->getCurrentStateId());
        }
        
        //add arrows
        foreach ($this->transitions as $tailNodeId => $arrowsData) {
            foreach ($arrowsData as $arrowId => $headNodeId) {
                $arrow = new Arrow();
                $arrow->id = $arrowId;
                $arrow->tail = $nodeById[$tailNodeId];
                $arrow->head = $nodeById[$headNodeId];
                $nodeById[$tailNodeId]->arrowsFromIt[] = $arrow;
            }
        }

        //return the root node
        return $nodeById[$this->initialStateId];
    }
    
    public function accept(StateVisitor $v)
    {
        return $this->getCurrentState()->accept($v);
    }

    public function handle($cmd)
    {
        $maybeRequest = $this->getCurrentState()->handle($cmd);
        if (!is_null($maybeRequest)) {
            switch (get_class($maybeRequest)) {
                case TransitionRequest::class:
                    $this->stateDataStorage->storeFor($this->id, new StateData(
                        uniqid(), 
                        $this->transitions[$this->getCurrentStateId()][$maybeRequest->edge], 
                        $maybeRequest->context
                    ));
                    break;
                case DestructionRequest::class:
                    $this->remove();
            }
        }
    }
    
    /**
     * @return State[]
     */
    public function getAllStates()
    {
        return array_values(array_map(function (StateData $stateData) {
            return $this->buildState(
                $stateData->id, 
                $stateData->stateId, 
                $stateData->context
            );
        }, $this->stateDataStorage->getAllFor(
            $this->id,
            $this->initialStateData
        )));
    }
    
    public function revertTo(State $s)
    {
        $abandonedStates = [];
        $isAbandoned = false;
        foreach ($this->getAllStates() as $possiblyAbandoned) {
            if ($possiblyAbandoned->getInstanceId() == $s->getInstanceId()) {
                $isAbandoned = true;
            }
            if ($isAbandoned) {
                $abandonedStates[] = $possiblyAbandoned;
            }
        }
        
        if (is_null($s->getInstanceId())) {
            $this->stateDataStorage->removeAllDataFor($this->id);
        } else {
            $this->stateDataStorage->revertFor($this->id, $s->getInstanceId());
        }
        
        foreach ($abandonedStates as $toClean) {
            $toClean->cleanUp();
        }
    }
    
    public function cleanUp()
    {
        foreach ($this->getAllStates() as $s) {
            $s->cleanUp();
        }
    }
    
    private function remove()
    {
        $this->cleanUp();
        $this->stateDataStorage->removeAllDataFor($this->id);
    }
    
    /**
     * @return State
     */
    private function getCurrentState()
    {
        $stateData = $this->stateDataStorage->getRecentFor(
            $this->id,
            $this->initialStateData
        );
        return $this->buildState(
            $stateData->id, 
            $stateData->stateId, 
            $stateData->context
        );
    }

    public function getInstanceId()
    {
        return $this->id;
    }
    
    public function getStateId()
    {
        return "rosmaro";
    }
    
    /**
     * @return String
     */
    private function getCurrentStateId()
    {
        return $this->stateDataStorage->getRecentFor(
            $this->id,
            $this->initialStateData
        )->stateId;
    }
    
    private function buildState($stateInstanceId, $stateId, Context $context)
    {
        $s = clone $this->statePrototypes[$stateId];
        $s->setContext($context);
        $s->setStateId($stateId);
        $s->setId($stateInstanceId);
        return $s;
    }
}