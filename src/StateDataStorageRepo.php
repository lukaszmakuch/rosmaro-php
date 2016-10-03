<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

interface StateDataStorageRepo
{
    /**
     * @param String $id
     * @return StateDataStorage
     */
    public function getExistingOrNewWith($id);
    public function removeBy($id);
}