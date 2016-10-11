<?php

namespace lukaszmakuch\Rosmaro;

class RosmaroTest extends \PHPUnit_Framework_TestCase
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
        $this->stateDataStorage = new InMemoryStorage();
    }

    public function testTransitions()
    {
        $r = $this->getRosmaro("a");

        $this->assertHashAppender($r, "");
        $r->addOneSymbol();

        $this->assertSymbolPrepender($r, "#");
        $r->prependSymbols(1);

        $this->assertHashAppender($r, "a#");
        $r->addOneSymbol();

        $this->assertSymbolPrepender($r, "a##");
        $r->prependSymbols(2);

        $this->assertSymbolPrepender($r, "aaa##");
        $r->prependSymbols(1);

        $this->assertSymbolPrepender($r, "baaa##");
        $r->prependSymbols(1);

        $this->assertSymbolPrepender($r, "bbaaa##");
    }

    public function testRevertingToState()
    {
        $r = $this->getRosmaro("a");

        //HashAppender ""
        $r->addOneSymbol();
        //SymbolPrepender "#"
        $r->prependSymbols(1);
        //HashAppender "a#"
        $r->addOneSymbol();
        //SymbolPrepender "a##"

        $this->assertCount(4, $r->history);
        $this->assertEquals(2, $this->howManyHashesAppended);

        $r->revertTo($r->history[1]['id']);
        $r->prependSymbols(2);

        $this->assertSymbolPrepender($r, "aa#");
        $this->assertEquals(1, $this->howManyHashesAppended);

        $r->revertToPreviousState();
        $r->revertToPreviousState();

        $this->assertHashAppender($r, '');
        $this->assertEquals(0, $this->howManyHashesAppended);

        $this->setExpectedExceptionRegExp(Error::class, '@no previous state@');
        $r->revertToPreviousState();
    }

    public function testIntegerId()
    {
        $fetchedId = $this->getRosmaro("a")->intId;
        $fetchedAgain = $this->getRosmaro("a")->intId;

        $this->assertInternalType('int', $fetchedId);
        $this->assertEquals($fetchedId, $fetchedAgain);
    }

    public function testDestruction()
    {
        $r = $this->getRosmaro("a");

        $r->addOneSymbol();
        $r->remove();

        $this->assertEquals(0, $this->howManyHashesAppended);
        $this->assertTrue($this->stateDataStorage->isEmptyFor("a"));
    }

    public function testManyIndependentInstances()
    {
        $r1 = $this->getRosmaro("a");
        $r2 = $this->getRosmaro("b");

        $r1->addOneSymbol();
        $r1->prependSymbols(1);

        $r2->addOneSymbol();
        $r2->prependSymbols(2);

        $this->assertHashAppender($r1, "a#");
        $this->assertSymbolPrepender($r2, "aa#");
    }

    public function testOneRosmaroById()
    {
        $fetchedFirst = $this->getRosmaro("a");
        $fetchedFirst->addOneSymbol();

        $fetchedLater = $this->getRosmaro("a");
        $fetchedLater->prependSymbols(1);

        $this->assertHashAppender($fetchedFirst, "a#");
        $this->assertHashAppender($fetchedLater, "a#");
    }

    public function testReadingGraph()
    {
        $r = $this->getRosmaro("a");
        $r->addOneSymbol();

        $appendHashNode = $r->graph;

        //expected nodes
        $expectedAppendHash = new GraphNode();
        $expectedAppendHash->id = "append_hash";

        $expectedPrependA = new GraphNode();
        $expectedPrependA->id = "prepend_a";
        $expectedPrependA->isCurrent = true;

        $expectedPrependB = new GraphNode();
        $expectedPrependB->id = "prepend_b";

        //expected arrows
        $expectedAppendHashToPrependA = new \stdClass();
        $expectedAppendHashToPrependA->id = "appended";
        $expectedAppendHashToPrependA->tail = $appendHashNode;
        $expectedAppendHashToPrependA->head = $expectedPrependA;

        $expectedPrependAToPrependB = new \stdClass();
        $expectedPrependAToPrependB->id = "prepended_more_than_1";
        $expectedPrependAToPrependB->tail = $expectedPrependA;
        $expectedPrependAToPrependB->head = $expectedPrependB;

        $expectedPrependAToAppendHash = new \stdClass();
        $expectedPrependAToAppendHash->id = "prepended_less_than_2";
        $expectedPrependAToAppendHash->tail = $expectedPrependA;
        $expectedPrependAToAppendHash->head = $expectedAppendHash;

        $expectedPrependBToPrependB1 = new \stdClass();
        $expectedPrependBToPrependB1->id = "prepended_more_than_1";
        $expectedPrependBToPrependB1->tail = $expectedPrependB;
        $expectedPrependBToPrependB1->head = $expectedPrependB;

        $expectedPrependBToPrependB2 = new \stdClass();
        $expectedPrependBToPrependB2->id = "prepended_less_than_2";
        $expectedPrependBToPrependB2->tail = $expectedPrependB;
        $expectedPrependBToPrependB2->head = $expectedPrependB;

        //connecting nodes with arrows
        $expectedAppendHash->arrowsFromIt[] = $expectedAppendHashToPrependA;
        $expectedPrependA->arrowsFromIt[] = $expectedPrependAToPrependB;
        $expectedPrependA->arrowsFromIt[] = $expectedPrependAToAppendHash;
        $expectedPrependB->arrowsFromIt[] = $expectedPrependBToPrependB1;
        $expectedPrependB->arrowsFromIt[] = $expectedPrependBToPrependB2;

        $this->assertEquals($expectedAppendHash, $appendHashNode);
    }

    private function getRosmaro($id)
    {
        $f = $this->rosmaroFactory;
        return $f($id);
    }

    private function assertHashAppender($s, $msg)
    {
        $this->assertEquals($msg, $s->getBuiltMessage());
    }

    private function assertSymbolPrepender($s, $msg)
    {
        $this->assertEquals($msg, $s->fetchMessage());
    }
}
