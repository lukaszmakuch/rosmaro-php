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
    private $initialStateId;
    private $stateDataStorage;
    private $transitions;
    private $statePrototypes;
    
    /**
     * @param type $initialStateId
     * @param array $transtions
     * @param StateTpl[] $statePrototypes
     * @param \lukaszmakuch\Rosmaro\StateDataStorage $stateDataStorage
     */
    public function __construct(
        $initialStateId,
        array $transtions,
        array $statePrototypes,
        \lukaszmakuch\Rosmaro\StateDataStorage $stateDataStorage
    ) {
        $this->stateDataStorage = $stateDataStorage;
        $this->transitions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->initialStateId = $initialStateId;
    }

    public function accept(StateVisitor $v)
    {
        return $this->getCurrentState()->accept($v);
    }

    public function handle($cmd)
    {
        $maybeTransitionRequest = $this->getCurrentState()->handle($cmd);
        if (!is_null($maybeTransitionRequest)) {
            $this->stateDataStorage->store(new StateData(
                uniqid(), 
                $this->transitions[$this->getCurrentStateId()][$maybeTransitionRequest->getEdge()], 
                $maybeTransitionRequest->getStateContext()
            ));
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
            $s = clone $this->statePrototypes[$stateData->getStateId()];
            $s->setId($stateData->getId());
            $s->setContext($stateData->getStateContext());
            return $s;
        }, $this->stateDataStorage->getAll())));
    }
    
    public function revertTo(State $s)
    {
        $this->stateDataStorage->revertTo($s->getId());
    }
    
    public function cleanUp()
    {
        foreach ($this->getAllStates() as $s) {
            $s->cleanUp();
        }
    }
    
    /**
     * @return State
     */
    private function getCurrentState()
    {
        try {
            $stateData = $this->stateDataStorage->getRecent();
            return $this->buildState(
                $stateData->getId(), 
                $stateData->getStateId(), 
                $stateData->getStateContext()
            );
        } catch (Exception\StateDataNotFound $e) {
            return $this->buildState(
                null, 
                $this->initialStateId, 
                new Context()
            );
        }
    }
    
    /**
     * @return String
     */
    private function getCurrentStateId()
    {
        try {
            return $this->stateDataStorage->getRecent()->getStateId();
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

    public function getId()
    {
        return null;
    }
}