<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

interface StateDataStorage
{
    /**
     * @param String $rosmaroId
     * @param StateData $stateDataToStoreIfNothingFound
     * @return StateData
     */
    public function getRecentFor($rosmaroId, StateData $stateDataToStoreIfNothingFound);
    
    /**
     * @param String $rosmaroId
     * @param StateData $stateDataToStoreIfNothingFound
     * @return StateData[] at least 1 element
     */
    public function getAllFor($rosmaroId, StateData $stateDataToStoreIfNothingFound);
    
    public function storeFor($rosmaroId, StateData $stateData);
    
    /**
     * @param String $stateDataId
     */
    public function revertFor($rosmaroId, $stateDataId);
    
    public function removeAllDataFor($rosmaroId);
}