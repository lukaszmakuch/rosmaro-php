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
    public $id;
    
    /**
     * 
     * @param String $id
     * @param boolean $isVisited
     * @param boolean $isCurrent
     */
    public function __construct($id, $isVisited, $isCurrent)
    {
        $this->id = $id;
        $this->isVisited = $isVisited;
        $this->isCurrent = $isCurrent;
    }
    
    /**
     * @param PathNode $another
     * @return boolean
     */
    public function equals(PathNode $another)
    {
        return (($this->id == $another->id)
            && ($this->isCurrent == $another->isCurrent)
            && ($this->isVisited == $another->isVisited));
    }
}