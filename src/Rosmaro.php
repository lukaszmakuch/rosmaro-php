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
    
    private $stateDataStorage;
    private $transitions;
    private $statePrototypes;
    
    /**
     * 
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
        
        try {
            $stateData = $stateDataStorage->get();
            $this->setCurrentState($stateData->getStateId(), $stateData->getStateContext());
        } catch (Exception\StateDataNotFound $e) {
            $this->setCurrentState($initialStateId, new Context());
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
            $this->setCurrentState(
                $this->transitions[$this->currentStateId][$maybeTransitionRequest->getEdge()],
                $maybeTransitionRequest->getStateContext()
            );
            $this->stateDataStorage->store(new StateData(
                null, 
                $this->currentStateId, 
                $maybeTransitionRequest->getStateContext()
            ));
        }
    }
    
    private function setCurrentState($stateId, Context $context)
    {
        $this->currentStateId = $stateId;
        $this->currentState = clone $this->statePrototypes[$stateId];
        $this->currentState->setContext($context);
    }
}