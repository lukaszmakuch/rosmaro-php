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
    /**
     * @var StateTpl
     */
    private $currentState;
    private $currentStateId;
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
        
        try {
            $stateData = $stateDataStorage->getRecent();
            $this->setCurrentState($stateData->getId(), $stateData->getStateId(), $stateData->getStateContext());
        } catch (Exception\StateDataNotFound $e) {
            $this->setCurrentState(null, $initialStateId, new Context());
        }
    }

    public function accept(StateVisitor $v)
    {
        return $this->currentState->accept($v);
    }

    public function handle($cmd)
    {
        $maybeTransitionRequest = $this->currentState->handle($cmd);
        if (!is_null($maybeTransitionRequest)) {
            $stateInstanceId = uniqid();
            $this->setCurrentState(
                $stateInstanceId,
                $this->transitions[$this->currentStateId][$maybeTransitionRequest->getEdge()],
                $maybeTransitionRequest->getStateContext()
            );
            $this->stateDataStorage->store(new StateData(
                $stateInstanceId, 
                $this->currentStateId, 
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
            $this->getState(null, $this->initialStateId, new Context())
        ], array_map(function (StateData $stateData) {
            $s = clone $this->statePrototypes[$stateData->getStateId()];
            $s->setId($stateData->getId());
            $s->setContext($stateData->getStateContext());
            return $s;
        }, $this->stateDataStorage->getAll())));
    }
    
    public function revertTo(State $s)
    {
        try {
            $this->stateDataStorage->revertTo($s->getId());
            $stateData = $this->stateDataStorage->getRecent();
            $this->setCurrentState($stateData->getId(), $stateData->getStateId(), $stateData->getStateContext());
        } catch (\lukaszmakuch\Rosmaro\Exception\StateDataNotFound $e) {
            $this->setCurrentState(null, $this->initialStateId, new Context());
        }
    }
    
    public function cleanUp()
    {
        foreach ($this->getAllStates() as $s) {
            $s->cleanUp();
        }
    }
    
    private function setCurrentState($stateInstanceId, $stateId, Context $context)
    {
        $this->currentStateId = $stateId;
        $this->currentState = $this->getState($stateInstanceId, $stateId, $context);
    }
    
    private function getState($stateInstanceId, $stateId, Context $context)
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