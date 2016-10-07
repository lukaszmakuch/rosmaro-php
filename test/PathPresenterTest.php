<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

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
        ], [], $getRosmaroWithVisited(["b", "a"]));
        
        $this->assertFlatRepresentation([
            new PathNode("a", true, false),
            new PathNode("d", true, true),
            new PathNode("e", false, false),
        ], [], $getRosmaroWithVisited(["d"]));
        
        $this->assertFlatRepresentation([
            new PathNode("a", true, true),
            new PathNode("d", false, false),
            new PathNode("f", false, false),
        ], ["a" => "a-d", "d" => "d-f"], $getRosmaroWithVisited([]));
    }
    
    /**
     * @param String[] $idsOfNodes like ["a", "b", ...]
     * @param String[] $arrows like ["a-b", "b-e", ...]
     * @param String[] $visitedNodes like ["a", "b", "a", ...]
     * @return Rosmaro;
     */
    private function getRosmaro(array $idsOfNodes, array $arrows, array $visitedNodes)
    {
        $rosmaroId = uniqid();
        $transitions = [];
        foreach ($arrows as $arrowData) {
            $tailHeadId = explode('-', $arrowData);
            if (!isset($transitions[$tailHeadId[0]])) {
                $transitions[$tailHeadId[0]] = [];
            }
            
            $transitions[$tailHeadId[0]][$arrowData] = $tailHeadId[1];
        }
        
        $prototypes = [];
        foreach ($idsOfNodes as $nodeId) {
            $prototypes[$nodeId] = new SymbolPrepender("?");
        }
        
        $rosmaroStorage = new StateDataStorages\InMemoryStateDataStorage();
        foreach (array_map(function ($stateId) {
            $s = new SymbolPrepender("a");
            $s->setId(uniqid());
            $s->setStateId($stateId);
            $s->setContext(new Context([]));
            return $s;
        }, $visitedNodes) as $s) {
            $rosmaroStorage->storeFor(
                $rosmaroId, 
                new StateData(
                    $s->getId(), 
                    $s->getStateId(), 
                    new Context([])
                )
            );
        }
        
        return new Rosmaro(
            $rosmaroId,
            reset($idsOfNodes),
            $transitions,
            $prototypes,
            $rosmaroStorage
        );
    }
    
    private function assertFlatRepresentation(array $expectedRepresentation, array $preferredArrows, Rosmaro $r)
    {
        $actualRepresentation = (new PathPresenter($preferredArrows))->getNodesOf($r);
        $this->assertEquals(
            $expectedRepresentation, 
            $actualRepresentation,
            sprintf(
                "expected:\n%s\ngot:\n%s",
                print_r($expectedRepresentation, true),
                print_r($actualRepresentation, true)
            )
        );
    }
}