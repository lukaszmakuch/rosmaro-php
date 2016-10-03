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
    private $dataStorageForInstances;
    private $initialStateId;
    private $transtions;
    private $statePrototypes;
    
    public function __construct(
        $initialStateId,
        array $transtions,
        array $statePrototypes,
        StateDataStorageRepo $dataStorageForInstances
    ) {
        $this->initialStateId = $initialStateId;
        $this->transtions = $transtions;
        $this->statePrototypes = $statePrototypes;
        $this->dataStorageForInstances = $dataStorageForInstances;
    }
    
    public function getStoredOrNewBy($id)
    {
        return new Rosmaro(
            $this->initialStateId, 
            $this->transtions, 
            $this->statePrototypes, 
            $this->dataStorageForInstances->getExistingOrNewWith($id)
        );
    }
    
    public function removeBy($id)
    {
        $this->getStoredOrNewBy($id)->cleanUp();
        $this->dataStorageForInstances->removeBy($id);
    }
}