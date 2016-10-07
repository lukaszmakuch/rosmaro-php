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
use lukaszmakuch\Rosmaro\PathPresenter\PathNode;
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
         *   /d-e
         *   \ \
         *    \_f_
         *      / \ 
         *      \_/
         */
        $getRosmaroWithVisited = function (array $nodes) {
            return $this->getRosmaro(
                ["a", "b", "c", "d", "e", "f"],
                ["a-b", "b-c", "a-d", "d-e", "d-f", 'f-d', 'f-f'],
                $nodes
            );
        };
        $this->assertFlatRepresentation([
            new PathNode("a", true, false),
            new PathNode("b", true, false),
            new PathNode("a", true, true),
            new PathNode("b", false, false),
            new PathNode("c", false, false),
        ], [], $getRosmaroWithVisited(["a", "b", "a"]));
        
        $this->assertFlatRepresentation([
            new PathNode("a", true, false),
            new PathNode("d", true, true),
            new PathNode("e", false, false),
        ], [], $getRosmaroWithVisited(["a", "d"]));
        
        $this->assertFlatRepresentation([
            new PathNode("a", true, true),
            new PathNode("d", false, false),
            new PathNode("f", false, false),
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