<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

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
        \lukaszmakuch\Rosmaro\StateDataStorage $stateDataStorage
    ) {
        $this->id = $id;
        $this->stateDataStorage = $stateDataStorage;
        $this->transitions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->initialStateId = $initialStateId;
    }
    
    /**
     * @return Graph\Node
     */
    public function getGraph()
    {
        $currentNodeId = $this->getCurrentStateId();
        //create all nodes
        $nodeById = [];
        $idsOfNodes = array_unique(array_merge(
            [$this->initialStateId], 
            array_keys($this->transitions))
        );
        foreach ($idsOfNodes as $nodeId) {
            $nodeById[$nodeId] = new Graph\Node();
            $nodeById[$nodeId]->id = $nodeId;
            $nodeById[$nodeId]->isCurrent = ($nodeId == $currentNodeId);
        }
        
        //add arrows
        foreach ($this->transitions as $headNodeId => $arrowsData) {
            foreach ($arrowsData as $arrowId => $tailNodeId) {
                $arrow = new Graph\Arrow();
                $arrow->id = $arrowId;
                $arrow->head = $nodeById[$headNodeId];
                $arrow->tail = $nodeById[$tailNodeId];
                $nodeById[$headNodeId]->arrowsFromIt[] = $arrow;
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
                case Request\TransitionRequest::class:
                    $this->stateDataStorage->storeFor($this->id, new StateData(
                        uniqid(), 
                        $this->transitions[$this->getCurrentStateId()][$maybeRequest->edge], 
                        $maybeRequest->context
                    ));
                    break;
                case Request\DestructionRequest::class:
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
            $s = clone $this->statePrototypes[$stateData->stateId];
            $s->setId($stateData->id);
            $s->setContext($stateData->context);
            return $s;
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
        } catch (Exception\StateDataNotFound $e) {
            return $this->buildState(
                null, 
                $this->initialStateId, 
                new Context()
            );
        }
    }

    public function getId()
    {
        return null;
    }
    
    /**
     * @return String
     */
    private function getCurrentStateId()
    {
        try {
            return $this->stateDataStorage->getRecentFor($this->id)->stateId;
        } catch (Exception\StateDataNotFound $e) {
            return $this->initialStateId;
        }
    }
    
    private function buildState($stateInstanceId, $stateId, Context $context)
    {
        $s = clone $this->statePrototypes[$stateId];
        $s->setContext($context);
        $s->setId($stateInstanceId);
        return $s;
    }
}