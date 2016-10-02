<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

abstract class StateTpl implements State, ContextUser
{
    /**
     * @var Context
     */
    protected $context;
    
    public function handle($cmd)
    {
        $this->throwExceptionIfInvalidContext();
        $this->throwExceptionIfUnsupported($cmd);
        return $this->handleImpl($cmd);
    }
    
    public function setContext(Context $c)
    {
        $this->context = $c;
    }
    
    public function accept(StateVisitor $v)
    {
        $v->visit($this);
    }
    
    protected abstract function getClassOfSupportedCommands();
    
    protected function throwExceptionIfInvalidContext()
    {
    }
    
    /**
     * @param mixed $cmd
     * @throws Exception\UnableToHandleCmd
     */
    protected function throwExceptionIfUnsupported($cmd)
    {
        $supportedClass = $this->getClassOfSupportedCommands();
        if (
            !is_object($cmd)
            || (!($cmd instanceof $supportedClass))
        ) {
            throw new Exception\UnableToHandleCmd();
        }
        
    }
    
    protected function causeTransition($transitionEdge, Context $nextStateConext)
    {
        return new TransitionRequest($transitionEdge, $nextStateConext);
    }
    
    /**
     * @throws Exception\UnableToHandleCmd
     * @return TransitionRequest|null
     */
    protected abstract function handleImpl($cmd);
}