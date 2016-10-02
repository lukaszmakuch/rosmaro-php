<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

class Context implements \Serializable
{
    private $values;
    
    public function __construct($values = [])
    {
        $this->values = $values;
    }
    
    /**
     * @param String $key
     * @return \Serializable|String|int|bool|float
     * @throws Exception\NotFoundInContext
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new Exception\NotFoundInContext();
        }
        
        return $this->values[$key];
    }
    
    /**
     * @param String $key
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }
    
    public function getCopyWith($differentValues)
    {
        return new Context(array_merge($this->values, $differentValues));
    }
    
    public function serialize()
    {
        return serialize($this->values);
    }

    public function unserialize($serialized)
    {
        $this->values = unserialize($serialized);
    }
}