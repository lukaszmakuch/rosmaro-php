<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Exception\UnableToHandleCmd;
use lukaszmakuch\Rosmaro\Request\TransitionRequest;

abstract class StateTpl implements State
{
    /**
     * @var Context
     */
    protected $context;
    
    /**
     * @var String|null null if hasn't been set yet
     */
    private $id;
    
    /**
     * @var String|null null if hasn't been set yet
     */
    private $stateId;
    
    public function __construct()
    {
        $this->context = new Context();
    }
    
    public function handle($cmd)
    {
        $this->throwExceptionIfInvalidContext();
        return $this->handleImpl($cmd);
    }
    
    public function setContext(Context $c)
    {
        $this->context = $c;
    }
    
    public function getInstanceId()
    {
        return $this->id;
    }
    
    /**
     * @param String $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getStateId()
    {
        return $this->stateId;
    }
    
    /**
     * @param String $stateId
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;
    }
    
    public function accept(StateVisitor $v)
    {
        return $v->visit($this);
    }
    
    public function cleanUp()
    {
    }
    
    /**
     * @throws UnableToHandleCmd
     */
    protected function throwExceptionIfInvalidContext()
    {
    }
    
    /**
     * @throws UnableToHandleCmd
     * @return TransitionRequest|null
     */
    protected abstract function handleImpl($cmd);
}