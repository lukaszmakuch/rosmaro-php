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
use lukaszmakuch\Rosmaro\Exception\UnableToHandleCmd;
use lukaszmakuch\Rosmaro\Graph\Arrow;
use lukaszmakuch\Rosmaro\Graph\Node;
use lukaszmakuch\Rosmaro\State\HashAppender;
use lukaszmakuch\Rosmaro\State\SymbolPrepender;
use lukaszmakuch\Rosmaro\StateDataStorages\InMemoryStateDataStorage;
use lukaszmakuch\Rosmaro\StateVisitors\CallableBasedVisitor;
use PHPUnit_Framework_TestCase;

class RosmaroTest extends PHPUnit_Framework_TestCase
{
    private $howManyHashesAppended = 0;
    private $rosmaroFactory;
    
    /**
     * @var InMemoryStateDataStorage
     */
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
        $this->stateDataStorage = new InMemoryStateDataStorage();
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
    
    public function testStateUnableToHandleRequest()
    {
        $r = $this->getRosmaro("a");
        $r->handle(new AddOneSymbol());
        
        $this->setExpectedExceptionRegExp(
            UnableToHandleCmd::class, 
            '@bad number@'
        );
        $r->handle(new PrependSymbols(99));
    }
    
    public function testUnsupportedCommand()
    {
        $r = $this->getRosmaro("a");
        $this->setExpectedExceptionRegExp(
            UnableToHandleCmd::class, 
            '@supports only.*AddOneSymbol.*PrependSymbols was given@'
        );
        $r->handle(new PrependSymbols(1));
    }
    
    public function testIncorrectContext()
    {
        $r = $this->getRosmaro("a");
        $r->handle(new AddOneSymbol());
        $r->handle(new PrependSymbols(49));
        $this->setExpectedExceptionRegExp(
            UnableToHandleCmd::class, 
            '@too long@'
        );
        //for this command the message in the context will be too long
        $r->handle(new PrependSymbols(1)); 
    }
    
    public function testVisitingActualStates()
    {
        $r = $this->getRosmaro("a");
        $visitedState = $r->accept(new CallableBasedVisitor(function (State $s) {
            return $s;
        }));
        $this->assertHashAppender($visitedState, '');
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
    
    public function testDestruction()
    {
        $r = $this->getRosmaro("a");
        
        $r->handle(new AddOneSymbol());
        $r->handle(new PrependSymbols(7)); //this caused a DestructionRequest
        
        $this->assertEquals(0, $this->howManyHashesAppended);
        $this->assertTrue($this->stateDataStorage->isEmptyFor("a"));
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
        $r = $this->getRosmaro("a");
        $r->handle(new AddOneSymbol());
        
        $appendHashNode = $r->getGraph();
        
        //expected nodes
        $expectedAppendHash = new Node();
        $expectedAppendHash->id = "append_hash";
        
        $expectedPrependA = new Node();
        $expectedPrependA->id = "prepend_a";
        $expectedPrependA->isCurrent = true;
        
        $expectedPrependB = new Node();
        $expectedPrependB->id = "prepend_b";
        
        //expected arrows
        $expectedAppendHashToPrependA = new Arrow();
        $expectedAppendHashToPrependA->id = "appended";
        $expectedAppendHashToPrependA->head = $appendHashNode;
        $expectedAppendHashToPrependA->tail = $expectedPrependA;
        
        $expectedPrependAToPrependB = new Arrow();
        $expectedPrependAToPrependB->id = "prepended_more_than_1";
        $expectedPrependAToPrependB->head = $expectedPrependA;
        $expectedPrependAToPrependB->tail = $expectedPrependB;
        
        $expectedPrependAToAppendHash = new Arrow();
        $expectedPrependAToAppendHash->id = "prepended_less_than_2";
        $expectedPrependAToAppendHash->head = $expectedPrependA;
        $expectedPrependAToAppendHash->tail = $expectedAppendHash;
        
        $expectedPrependBToPrependB1 = new Arrow();
        $expectedPrependBToPrependB1->id = "prepended_more_than_1";
        $expectedPrependBToPrependB1->head = $expectedPrependB;
        $expectedPrependBToPrependB1->tail = $expectedPrependB;
        
        $expectedPrependBToPrependB2 = new Arrow();
        $expectedPrependBToPrependB2->id = "prepended_less_than_2";
        $expectedPrependBToPrependB2->head = $expectedPrependB;
        $expectedPrependBToPrependB2->tail = $expectedPrependB;
        
        //connecting nodes with arrows
        $expectedAppendHash->arrowsFromIt[] = $expectedAppendHashToPrependA;
        $expectedPrependA->arrowsFromIt[] = $expectedPrependAToPrependB;
        $expectedPrependA->arrowsFromIt[] = $expectedPrependAToAppendHash;
        $expectedPrependB->arrowsFromIt[] = $expectedPrependBToPrependB1;
        $expectedPrependB->arrowsFromIt[] = $expectedPrependBToPrependB2;
        
        $this->assertEquals($expectedAppendHash, $appendHashNode);
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