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
    public function testTransitions()
    {
        $r = new Rosmaro(
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
        
        $assertHashAppenderWith = function ($msg) use ($r) {
            $this->assertState($r, HashAppender::class, function (HashAppender $s) use ($msg) { return (
                $s->getBuiltMessage() == $msg
            ); });
        };
        
        $assertSymbolPrependerWith = function ($msg) use ($r) {
            $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) use ($msg) { return (
                $s->fetchMessage() == $msg
            ); });
        }; 
        
        $assertHashAppenderWith("");
        $r->handle(new AddOneSymbol());
        
        $assertSymbolPrependerWith("#");
        $r->handle(new PrependSymbols(1));
        
        $assertHashAppenderWith("a#");
        $r->handle(new AddOneSymbol());
        
        $assertSymbolPrependerWith("a##");
        $r->handle(new PrependSymbols(2));
        
        $assertSymbolPrependerWith("aaa##");
        $r->handle(new PrependSymbols(1));
        
        $assertSymbolPrependerWith("baaa##");
        $r->handle(new PrependSymbols(1));
        
        $assertSymbolPrependerWith("bbaaa##");
    }
    
    private function assertState(State $s, $expectedClass, callable $acceptor)
    {
        $s->accept(new CallableBasedVisitor(function (State $actualState) use ($expectedClass, $acceptor) {
            $this->assertInstanceOf($expectedClass, $actualState);
            $this->assertTrue($acceptor($actualState));
        }));
    }
}