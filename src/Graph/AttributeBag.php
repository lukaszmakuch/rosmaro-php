<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Graph;

class AttributeBag
{
    private $attrs = [];
    
    /**
     * @param String $name
     * @param mixed $value
     */
    public function setAttr($name, $value)
    {
        $this->attrs[$name] = $value;
    }
    
    /**
     * @param String $name
     * @return mixed null if not found
     */
    public function getAttr($name)
    {
        return isset($this->attrs[$name])
            ? $this->attrs[$name]
            : null;
    }
}