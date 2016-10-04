<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class Arrow extends AttributeBag
{
    public $head;
    public $tail;
    
    /**
     * @return Node
     */
    public function getHead()
    {
        return $this->head;
    }
    
    /**
     * @return Node
     */
    public function getTail()
    {
        return $this->tail;
    }
}

