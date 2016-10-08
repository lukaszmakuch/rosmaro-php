<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Request;

class ReversionRequest
{
    /**
     * @var String
     */
    public $stateInstanceId;
    
    /**
     * @param String $stateInstanceId
     */
    public function __construct($stateInstanceId)
    {
        $this->stateInstanceId = $stateInstanceId;
    }
}