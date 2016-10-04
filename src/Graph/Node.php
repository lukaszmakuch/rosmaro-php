<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class Node extends AttributeBag
{
    public $arrowsFromIt = [];
    
    /**
     * @return Arrow[]
     */
    public function getArrowsFromIt()
    {
        return $this->arrowsFromIt;
    }
}

