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
    
    public function getAllFor($rosmaroId)
    {
        return isset($this->stateDataStackByRosmaroId[$rosmaroId])
            ? $this->stateDataStackByRosmaroId[$rosmaroId]
            : [];
    }

    public function getRecentFor($rosmaroId)
    {
        if (!isset($this->stateDataStackByRosmaroId[$rosmaroId])) {
            throw new StateDataNotFound();
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
        foreach ($this->getAllFor($rosmaroId) as $stateDataFromOldStack) {
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