<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Graph\Arrow;
use lukaszmakuch\Rosmaro\Graph\Node;
use lukaszmakuch\Rosmaro\PathPresenter\FlatNode;
use lukaszmakuch\Rosmaro\PathPresenter\PathPresenter;
use lukaszmakuch\Rosmaro\State\SymbolPrepender;
use PHPUnit_Framework_TestCase;

class PathPresenterTest extends PHPUnit_Framework_TestCase
{
    public function testPreseting()
    {
        /**
         *    b-c 
         *   /    
         *  a     
         *   \    
         *    d-e
         *     \
         *      f
         */
        $getRosmaroWithVisited = function (array $nodes) {
            return $this->getRosmaro(
                ["a", "b", "c", "d", "e", "f"],
                ["a-b", "b-c", "a-d", "d-e", "d-f"],
                $nodes
            );
        };
        $this->assertFlatRepresentation([
            new FlatNode("a", true, false),
            new FlatNode("b", true, false),
            new FlatNode("a", true, true),
            new FlatNode("b", false, false),
            new FlatNode("c", false, false),
        ], [], $getRosmaroWithVisited(["a", "b", "a"]));
        
        $this->assertFlatRepresentation([
            new FlatNode("a", true, false),
            new FlatNode("d", true, true),
            new FlatNode("e", false, false),
        ], [], $getRosmaroWithVisited(["a", "d"]));
        
        $this->assertFlatRepresentation([
            new FlatNode("a", true, true),
            new FlatNode("d", false, false),
            new FlatNode("f", false, false),
        ], ["a" => "a-d", "d" => "d-f"], $getRosmaroWithVisited(["a"]));
    }
    
    /**
     * @param String[] $idsOfNodes like ["a", "b", ...]
     * @param String[] $arrows like ["a-b", "b-e", ...]
     * @param String[] $visitedNodes like ["a", "b", "a", ...]
     * @return Rosmaro;
     */
    private function getRosmaro(array $idsOfNodes, array $arrows, array $visitedNodes)
    {
        $getState = function ($stateId) {
            $s = new SymbolPrepender("a");
            $s->setId($stateId);
            $s->setContext(new Context([]));
            return $s;
        };
        
        $rosmaro = $this->createMock(Rosmaro::class);
        $rosmaro->method('getAllStates')->will($this->returnValue(array_map($getState, $visitedNodes)));
        
        $nodes = [];
        foreach ($idsOfNodes as $nodeId) {
            $nodes[$nodeId] = new Node();
            $nodes[$nodeId]->id = $nodeId;
        }
        
        foreach ($arrows as $arrowData) {
            $a = new Arrow();
            $a->id = $arrowData;
            $tailHeadId = explode('-', $arrowData);
            $a->tail = $nodes[$tailHeadId[0]];
            $a->head = $nodes[$tailHeadId[1]];
            $nodes[$tailHeadId[0]]->arrowsFromIt[] = $a;
        }
        
        $rosmaro->method('getGraph')->will($this->returnValue($nodes[$idsOfNodes[0]]));
        return $rosmaro;
    }
    
    private function assertFlatRepresentation(array $expectedRepresentation, array $preferredArrows, Rosmaro $r)
    {
        $this->assertEquals(
            $expectedRepresentation, 
            (new PathPresenter($preferredArrows))->getNodesOf($r)
        );
    }
}