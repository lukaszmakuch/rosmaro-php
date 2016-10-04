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
    private $rosmaroFactory;
    private $stateDataStorage;
    
    protected function setUp()
    {
        $this->rosmaroFactory = function ($rosmaroId) {
            return new Rosmaro(
                $rosmaroId,
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
                $this->stateDataStorage
            );
        };
        $this->stateDataStorage = new StateDataStorages\InMemoryStateDataStorage();
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
    
    public function testReadingGraph()
    {
        $appendHashNode = $this->getRosmaro("a")->getGraph();
        $this->assertEquals("append_hash", $appendHashNode->getAttr("id"));
        $arrowsFromAppendHashNode = $appendHashNode->getArrowsFromIt();
        $this->assertCount(1, $arrowsFromAppendHashNode);
        $appendedArrowFromAppendHashNode = $arrowsFromAppendHashNode[0];
        $this->assertEquals("appended", $appendedArrowFromAppendHashNode->getAttr("id"));
        $this->assertSame($appendHashNode, $appendedArrowFromAppendHashNode->getHead());
        
        $prependANode = $appendedArrowFromAppendHashNode->getTail();
        $this->assertEquals("prepend_a", $prependANode->getAttr("id"));
        $arrowsFromPrependANode = $prependANode->getArrowsFromIt();
        $this->assertCount(2, $arrowsFromPrependANode);
        $moreThan1ArrowFromPrependA = $arrowsFromPrependANode[0];
        $this->assertEquals("prepended_more_than_1", $moreThan1ArrowFromPrependA->getAttr("id"));
        $this->assertSame($prependANode, $moreThan1ArrowFromPrependA->getHead());
        $lessThan2ArrowFromPrependA = $arrowsFromPrependANode[1];
        $this->assertEquals("prepended_less_than_2", $lessThan2ArrowFromPrependA->getAttr("id"));
        $this->assertSame($prependANode, $lessThan2ArrowFromPrependA->getHead());
        $this->assertSame($appendHashNode, $lessThan2ArrowFromPrependA->getTail());
        
        $prependBNode = $moreThan1ArrowFromPrependA->getTail();
        $this->assertEquals("prepend_b", $prependBNode->getAttr("id"));
        $arrowsFromPrependBNode = $prependBNode->getArrowsFromIt();
        $this->assertCount(2, $arrowsFromPrependBNode);
        $moreThan1ArrowFromPrependB = $arrowsFromPrependBNode[0];
        $this->assertEquals("prepended_more_than_1", $moreThan1ArrowFromPrependB->getAttr("id"));
        $this->assertSame($prependBNode, $moreThan1ArrowFromPrependB->getHead());
        $this->assertSame($prependBNode, $moreThan1ArrowFromPrependB->getTail());
        $lessThan2ArrowFromPrependB = $arrowsFromPrependBNode[1];
        $this->assertEquals("prepended_less_than_2", $lessThan2ArrowFromPrependB->getAttr("id"));
        $this->assertSame($prependBNode, $lessThan2ArrowFromPrependB->getHead());
        $this->assertSame($prependBNode, $lessThan2ArrowFromPrependB->getTail());
    }
    
    /**
     * @param String $id
     * @return Rosmaro
     */
    private function getRosmaro($id)
    {
        $f = $this->rosmaroFactory;
        return $f($id);
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