<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\PathPresenter;

class PathNode
{
    /**
     * @var boolean
     */
    public $isVisited;
    
    /**
     * @var boolean
     */
    public $isCurrent;
    
    /**
     * @var String
     */
    public $stateId;
    
    /**
     * @var String|null
     */
    public $stateInstanceIdIfStored;
    
    /**
     * @param String|null $stateInstanceIdIfStored
     * @param String $stateId
     * @param boolean $isVisited
     * @param boolean $isCurrent
     */
    public function __construct(
        $stateInstanceIdIfStored, 
        $stateId, 
        $isVisited, 
        $isCurrent
    ) {
        $this->stateInstanceIdIfStored = $stateInstanceIdIfStored;
        $this->stateId = $stateId;
        $this->isVisited = $isVisited;
        $this->isCurrent = $isCurrent;
    }
    
    /**
     * @param PathNode $another
     * @return boolean
     */
    public function equals(PathNode $another)
    {
        return (($this->stateId == $another->stateId)
            && ($this->stateInstanceIdIfStored == $another->stateInstanceIdIfStored)
            && ($this->isCurrent == $another->isCurrent)
            && ($this->isVisited == $another->isVisited));
    }
}