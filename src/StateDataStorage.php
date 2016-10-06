<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Exception\StateDataNotFound;

interface StateDataStorage
{
    /**
     * @return StateData
     * @throws StateDataNotFound
     */
    public function getRecentFor($rosmaroId);
    
    /**
     * @return StateData[]
     */
    public function getAllFor($rosmaroId);
    
    public function storeFor($rosmaroId, StateData $stateData);
    
    /**
     * @param String $stateDataId
     */
    public function revertFor($rosmaroId, $stateDataId);
    
    public function removeAllDataFor($rosmaroId);
}