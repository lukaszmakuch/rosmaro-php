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
    public function get();
    public function store(StateData $stateData);
}