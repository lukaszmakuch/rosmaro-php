<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateDataStorages;

use lukaszmakuch\Rosmaro\Exception\StateDataNotFound;
use lukaszmakuch\Rosmaro\StateData;
use lukaszmakuch\Rosmaro\StateDataStorage;

class InMemoryStateDataStorage implements StateDataStorage
{
    private $stateDataStackByRosmaroId = [];
    
    /**
     * @param String $rosmaroId
     * @return boolean
     */
    public function isEmptyFor($rosmaroId)
    {
        return !isset($this->stateDataStackByRosmaroId[$rosmaroId]);
    }
    
    public function getAllFor($rosmaroId, StateData $stateDataToStoreIfNothingFound)
    {
        if (!isset($this->stateDataStackByRosmaroId[$rosmaroId])) {
            $this->stateDataStackByRosmaroId[$rosmaroId] = [$stateDataToStoreIfNothingFound];
        }
        
        return $this->stateDataStackByRosmaroId[$rosmaroId];
    }

    public function getRecentFor($rosmaroId, StateData $stateDataToStoreIfNothingFound)
    {
        if (!isset($this->stateDataStackByRosmaroId[$rosmaroId])) {
            $this->stateDataStackByRosmaroId[$rosmaroId] = [$stateDataToStoreIfNothingFound];
        }
        
        return end($this->stateDataStackByRosmaroId[$rosmaroId]);
    }

    public function removeAllDataFor($rosmaroId)
    {
        unset($this->stateDataStackByRosmaroId[$rosmaroId]);
    }

    public function revertFor($rosmaroId, $stateDataId)
    {
        $newStack = [];
        foreach ($this->stateDataStackByRosmaroId[$rosmaroId] as $stateDataFromOldStack) {
            $newStack[] = $stateDataFromOldStack;
            if ($stateDataFromOldStack->id == $stateDataId) {
                $this->stateDataStackByRosmaroId[$rosmaroId] = $newStack;
                break;
            }
        }
    }

    public function storeFor($rosmaroId, StateData $stateData)
    {
        if (!isset($this->stateDataStackByRosmaroId[$rosmaroId])) {
            $this->stateDataStackByRosmaroId[$rosmaroId] = [];
        }
        
        $this->stateDataStackByRosmaroId[$rosmaroId][] = $stateData;
    }
}