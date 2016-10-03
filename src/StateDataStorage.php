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
     * @return StateData
     * @throws Exception\StateDataNotFound
     */
    public function getRecent();
    
    /**
     * @return StateDate[]
     */
    public function getAll();
    
    public function store(StateData $stateData);
    
    /**
     * @param String $stateDataId
     */
    public function revertTo($stateDataId);
    
    public function removeAllData();
}