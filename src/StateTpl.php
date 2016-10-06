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
    
    public function __construct()
    {
        $this->context = new Context();
    }
    
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
    
    public function getId()
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
    
    public function accept(StateVisitor $v)
    {
        return $v->visit($this);
    }
    
    public function cleanUp()
    {
    }
    
    protected abstract function getClassOfSupportedCommands();
    
    /**
     * @throws UnableToHandleCmd
     */
    protected function throwExceptionIfInvalidContext()
    {
    }
    
    /**
     * @param mixed $cmd
     * @throws UnableToHandleCmd
     */
    protected function throwExceptionIfUnsupported($cmd)
    {
        $supportedClass = $this->getClassOfSupportedCommands();
        if (
            !is_object($cmd)
            || (!($cmd instanceof $supportedClass))
        ) {
            throw new UnableToHandleCmd(sprintf(
                "%s supports only %s, but %s was given",
                get_class($this),
                $supportedClass,
                get_class($cmd)
            ));
        }
    }
    
    /**
     * @throws UnableToHandleCmd
     * @return TransitionRequest|null
     */
    protected abstract function handleImpl($cmd);
}