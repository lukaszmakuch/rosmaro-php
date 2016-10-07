<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Exception\StateDataNotFound;
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
    }
    
    /**
     * @return Node
     */
    public function getGraph()
    {
        $currentNodeId = $this->getCurrentStateId();
        //create all nodes
        $nodeById = [];
        $idsOfNodes = array_keys($this->statePrototypes);
        foreach ($idsOfNodes as $nodeId) {
            $nodeById[$nodeId] = new Node();
            $nodeById[$nodeId]->id = $nodeId;
            $nodeById[$nodeId]->isCurrent = ($nodeId == $currentNodeId);
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
        return array_values(array_merge([
            $this->buildState(null, $this->initialStateId, new Context())
        ], array_map(function (StateData $stateData) {
            return $this->buildState(
                $stateData->id, 
                $stateData->stateId, 
                $stateData->context
            );
        }, $this->stateDataStorage->getAllFor($this->id))));
    }
    
    public function revertTo(State $s)
    {
        $abandonedStates = [];
        $isAbandoned = false;
        foreach ($this->getAllStates() as $possiblyAbandoned) {
            if ($possiblyAbandoned->getId() == $s->getId()) {
                $isAbandoned = true;
            }
            if ($isAbandoned) {
                $abandonedStates[] = $possiblyAbandoned;
            }
        }
        
        if (is_null($s->getId())) {
            $this->stateDataStorage->removeAllDataFor($this->id);
        } else {
            $this->stateDataStorage->revertFor($this->id, $s->getId());
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
        try {
            $stateData = $this->stateDataStorage->getRecentFor($this->id);
            return $this->buildState(
                $stateData->id, 
                $stateData->stateId, 
                $stateData->context
            );
        } catch (StateDataNotFound $e) {
            return $this->buildState(
                null, 
                $this->initialStateId, 
                new Context()
            );
        }
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getStateId()
    {
        return null;
    }
    
    /**
     * @return String
     */
    public function getCurrentStateId()
    {
        try {
            return $this->stateDataStorage->getRecentFor($this->id)->stateId;
        } catch (StateDataNotFound $e) {
            return $this->initialStateId;
        }
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