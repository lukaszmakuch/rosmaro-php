<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\PathPresenter;

class FlatNode
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
}