<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\StateDataStorages;

class InMemoryStateDataStorageRepo implements \lukaszmakuch\Rosmaro\StateDataStorageRepo
{
    private $storages = [];
    
    public function getExistingOrNewWith($id)
    {
        if (!isset($this->storages[$id])) {
            $this->storages[$id] = new InMemoryStateDataStorage();
        }
        
        return $this->storages[$id];
    }

    public function removeBy($id)
    {
        unset($this->storages[$id]);
    }
}