<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateDataStorages;

class InMemoryStateDataStorage implements \lukaszmakuch\Rosmaro\StateDataStorage
{
    private $stateDataById = [];
    
    public function getRecent()
    {
        if (empty($this->stateDataById)) {
            throw new \lukaszmakuch\Rosmaro\Exception\StateDataNotFound();
        }
        
        return end($this->stateDataById);
    }

    public function store(\lukaszmakuch\Rosmaro\StateData $stateData)
    {
        $this->stateDataById[$stateData->getId()] = $stateData;
    }

    public function getAll()
    {
        return $this->stateDataById;
    }

    public function revertTo($stateDataId)
    {
        if (is_null($stateDataId)) {
            $this->removeAllData();
            return;
        }
        
        $newStack = [];
        foreach ($this->stateDataById as $stateDataId => $oldStateData) {
            $newStack[$stateDataId] = $oldStateData;
            if ($stateDataId == $stateDataId) {
                break;
            }
        }
        
        $this->stateDataById = $newStack;
    }

    public function removeAllData()
    {
        $this->stateDataById = [];
    }
}