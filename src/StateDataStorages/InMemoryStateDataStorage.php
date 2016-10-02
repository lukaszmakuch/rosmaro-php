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
    private $stateData = null;
    
    public function get()
    {
        if (is_null($this->stateData)) {
            throw new \lukaszmakuch\Rosmaro\Exception\StateDataNotFound();
        }
        
        return $this->stateData;
    }

    public function store(\lukaszmakuch\Rosmaro\StateData $stateData)
    {
        $this->stateData = $stateData;
    }
}