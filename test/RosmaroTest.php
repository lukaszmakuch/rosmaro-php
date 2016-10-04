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
use lukaszmakuch\Rosmaro\StateVisitors\CallableBasedVisitor;
use PHPUnit_Framework_TestCase;

class RosmaroTest extends PHPUnit_Framework_TestCase
{
    private $howManyHashesAppended = 0;
    
    /**
     * @var RosmaroStorage
     */
    private $rosmaroStorage;
    
    protected function setUp()
    {
        $this->rosmaroStorage = new RosmaroStorage(
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
                "append_hash" => new HashAppender($this->howManyHashesAppended),
                "prepend_a" => new SymbolPrepender("a"),
                "prepend_b" => new SymbolPrepender("b"),
            ],
            new StateDataStorages\InMemoryStateDataStorage()
        );
    }
    
    public function testTransitions()
    {
        $r = $this->getRosmaro("a");
        
        $this->assertHashAppender($r, "");
        $r->handle(new AddOneSymbol());
        
        $this->assertSymbolPrepender($r, "#");
        $r->handle(new PrependSymbols(1));
        
        $this->assertHashAppender($r, "a#");
        $r->handle(new AddOneSymbol());
        
        $this->assertSymbolPrepender($r, "a##");
        $r->handle(new PrependSymbols(2));
        
        $this->assertSymbolPrepender($r, "aaa##");
        $r->handle(new PrependSymbols(1));
        
        $this->assertSymbolPrepender($r, "baaa##");
        $r->handle(new PrependSymbols(1));
        
        $this->assertSymbolPrepender($r, "bbaaa##");
    }
    
    public function testRevertingToState()
    {
        $r = $this->getRosmaro("a");
        
        //HashAppender ""
        $r->handle(new AddOneSymbol());
        //SymbolPrepender "#"
        $r->handle(new PrependSymbols(1));
        //HashAppender "a#"
        $r->handle(new AddOneSymbol());
        //SymbolPrepender "a##"
        
        $allStates = $r->getAllStates();
        $this->assertCount(4, $allStates);
        $this->assertHashAppender($allStates[0], "");
        $this->assertSymbolPrepender($allStates[1], "#");
        $this->assertHashAppender($allStates[2], "a#");
        $this->assertSymbolPrepender($allStates[3], "a##");
        $this->assertEquals(2, $this->howManyHashesAppended);
        
        $r->revertTo($allStates[1]);
        $r->handle(new PrependSymbols(2));
        $this->assertSymbolPrepender($r, "aa#");
        $this->assertEquals(1, $this->howManyHashesAppended);
        
        $r->revertTo($allStates[0]);
        $this->assertHashAppender($r, "");
        $this->assertEquals(0, $this->howManyHashesAppended);
    }

    public function testCleanUp()
    {
        $r = $this->getRosmaro("a");
        
        $r->handle(new AddOneSymbol());
        $r->handle(new PrependSymbols(1));
        $r->handle(new AddOneSymbol());
        
        $this->assertEquals(2, $this->howManyHashesAppended);
        $r->cleanUp();
        $this->assertEquals(0, $this->howManyHashesAppended);
    }
    
    public function testManyIndependentInstances()
    {
        $r1 = $this->getRosmaro("a");
        $r2 = $this->getRosmaro("b");
        
        $r1->handle(new AddOneSymbol());
        $r1->handle(new PrependSymbols(1));
        
        $r2->handle(new AddOneSymbol());
        $r2->handle(new PrependSymbols(2));
        
        $this->assertHashAppender($r1, "a#");
        $this->assertSymbolPrepender($r2, "aa#");
    }
    
    public function testOneRosmaroById()
    {
        $fetchedFirst = $this->getRosmaro("a");
        $fetchedFirst->handle(new AddOneSymbol());
        
        $fetchedLater = $this->getRosmaro("a");
        $fetchedLater->handle(new PrependSymbols(1));
        
        $this->assertHashAppender($fetchedFirst, "a#");
        $this->assertHashAppender($fetchedLater, "a#");
    }
    
    /**
     * @param String $id
     * @return Rosmaro
     */
    private function getRosmaro($id)
    {
        return $this->rosmaroStorage->getStoredOrNewBy($id);
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