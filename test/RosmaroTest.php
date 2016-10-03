<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Cmd\AddOneSymbol;
use lukaszmakuch\Rosmaro\Cmd\PrependSymbols;
use lukaszmakuch\Rosmaro\State\HashAppender;
use lukaszmakuch\Rosmaro\State\SymbolPrepender;
use lukaszmakuch\Rosmaro\StateDataStorages\InMemoryStateDataStorage;
use lukaszmakuch\Rosmaro\StateVisitors\CallableBasedVisitor;
use PHPUnit_Framework_TestCase;

class RosmaroTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rosmaro
     */
    private $r;
    
    protected function setUp()
    {
        $this->r = new Rosmaro(
            "append_hash",
            [
                "append_hash" => [
                    "appended" => "prepend_a", 
                ],
                "prepend_a" => [
                    "prepended_more_than_1" => "prepend_b",
                    "prepended_less_than_2" => "append_hash",
                ],
                "prepend_b" => [
                    "prepended_more_than_1" => "prepend_b",
                    "prepended_less_than_2" => "prepend_b",
                ],
            ],
            [
                "append_hash" => new HashAppender(),
                "prepend_a" => new SymbolPrepender("a"),
                "prepend_b" => new SymbolPrepender("b"),
            ],
            new InMemoryStateDataStorage()
        );
    }
    
    public function testTransitions()
    {
        $this->assertHashAppender($this->r, "");
        $this->r->handle(new AddOneSymbol());
        
        $this->assertSymbolPrepender($this->r, "#");
        $this->r->handle(new PrependSymbols(1));
        
        $this->assertHashAppender($this->r, "a#");
        $this->r->handle(new AddOneSymbol());
        
        $this->assertSymbolPrepender($this->r, "a##");
        $this->r->handle(new PrependSymbols(2));
        
        $this->assertSymbolPrepender($this->r, "aaa##");
        $this->r->handle(new PrependSymbols(1));
        
        $this->assertSymbolPrepender($this->r, "baaa##");
        $this->r->handle(new PrependSymbols(1));
        
        $this->assertSymbolPrepender($this->r, "bbaaa##");
    }
    
    public function testRevertingToState()
    {
        //HashAppender ""
        $this->r->handle(new AddOneSymbol());
        //SymbolPrepender "#"
        $this->r->handle(new PrependSymbols(1));
        //HashAppender "a#"
        $this->r->handle(new AddOneSymbol());
        //SymbolPrepender "a##"
        
        $allStates = $this->r->getAllStates();
        $this->assertCount(4, $allStates);
        $this->assertHashAppender($allStates[0], "");
        $this->assertSymbolPrepender($allStates[1], "#");
        $this->assertHashAppender($allStates[2], "a#");
        $this->assertSymbolPrepender($allStates[3], "a##");
        
        $this->r->revertTo($allStates[1]);
        $this->r->handle(new PrependSymbols(2));
        $this->assertSymbolPrepender($this->r, "aa#");
    }
    
    private function assertHashAppender(State $s, $msg)
    {
        $this->assertState($s, HashAppender::class, function (HashAppender $s) use ($msg) { return (
            $s->getBuiltMessage() == $msg
        ); });
    }
    
    private function assertSymbolPrepender(State $s, $msg)
    {
        $this->assertState($s, SymbolPrepender::class, function (SymbolPrepender $s) use ($msg) { return (
            $s->fetchMessage() == $msg
        ); });
    }
    
    private function assertState(State $s, $expectedClass, callable $acceptor)
    {
        $s->accept(new CallableBasedVisitor(function (State $actualState) use ($expectedClass, $acceptor) {
            $this->assertInstanceOf($expectedClass, $actualState);
            $this->assertTrue($acceptor($actualState));
        }));
    }
}