<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\State\HashAppender;
use lukaszmakuch\Rosmaro\State\SymbolPrepender;

class RosmaroTest extends \PHPUnit_Framework_TestCase
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
            new StateDataStorages\InMemoryStateDataStorage()
        );
        
        $this->assertState($r, HashAppender::class, function (HashAppender $s) { return (
            $s->getBuiltMessage() == ""
        ); });
        
        $r->handle(new Cmd\AddOneSymbol());
        
        $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) { return (
            $s->fetchMessage() == "#"
        ); });
        
        $r->handle(new Cmd\PrependSymbols(1));
        
        $this->assertState($r, HashAppender::class, function (HashAppender $s) { return (
            $s->getBuiltMessage() == "a#"
        ); });
        
        $r->handle(new Cmd\AddOneSymbol());
        
        $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) { return (
            $s->fetchMessage() == "a##"
        ); });
        
        $r->handle(new Cmd\PrependSymbols(2));
        
        $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) { return (
            $s->fetchMessage() == "aaa##"
        ); });
        
        $r->handle(new Cmd\PrependSymbols(1));
        
        $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) { return (
            $s->fetchMessage() == "baaa##"
        ); });
        
        $r->handle(new Cmd\PrependSymbols(1));
        
        $this->assertState($r, SymbolPrepender::class, function (SymbolPrepender $s) { return (
            $s->fetchMessage() == "bbaaa##"
        ); });
    }
    
    private function assertState(State $s, $expectedClass, callable $acceptor)
    {
        $s->accept(new StateVisitors\CallableBasedVisitor(function (State $actualState) use ($expectedClass, $acceptor) {
            $this->assertInstanceOf($expectedClass, $actualState);
            $this->assertTrue($acceptor($actualState));
        }));
    }
}