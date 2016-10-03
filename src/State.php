<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

interface State
{
    /**
     * @param mixed $cmd
     * @return TransitionRequest|null
     */
    public function handle($cmd);
    
    /**
     * @return String|null
     */
    public function getId();
    
    public function accept(StateVisitor $v);
    
    public function cleanUp();
}