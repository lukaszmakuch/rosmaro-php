<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

class RosmaroStorage
{
    private $dataStorage;
    private $initialStateId;
    private $transtions;
    private $statePrototypes;
    
    public function __construct(
        $initialStateId,
        array $transtions,
        array $statePrototypes,
        StateDataStorage $dataStorage
    ) {
        $this->initialStateId = $initialStateId;
        $this->transtions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->dataStorage = $dataStorage;
    }
    
    public function getStoredOrNewBy($id)
    {
        return new Rosmaro(
            $id,
            $this->initialStateId, 
            $this->transtions, 
            $this->statePrototypes, 
            $this->dataStorage
        );
    }
    
    public function removeBy($id)
    {
        $this->getStoredOrNewBy($id)->cleanUp();
        $this->dataStorage->removeAllDataFor($id);
    }
}