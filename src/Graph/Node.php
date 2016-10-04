<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class Node
{
    /**
     * @var Arrow[] 
     */
    public $arrowsFromIt = [];
    
    /**
     * @var boolean
     */
    public $isCurrent = false;

    /**
     * @var String
     */
    public $id = "";
}

