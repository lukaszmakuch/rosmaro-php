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
            new PathNode("obj1", "a", true, false),
            new PathNode("obj2", "b", true, false),
            new PathNode("obj3", "a", true, true),
            new PathNode(null, "b", false, false),
            new PathNode(null, "c", false, false),
        ], [], $getRosmaroWithVisited(["obj1" => "a", "obj2" => "b", "obj3" => "a"]));
        
        $this->assertFlatRepresentation([
            new PathNode("obj1", "a", true, false),
            new PathNode("obj2", "d", true, true),
            new PathNode(null, "e", false, false),
        ], [], $getRosmaroWithVisited(["obj1" => "a", "obj2" => "d"]));
        
        $this->assertFlatRepresentation([
            new PathNode("obj1", "a", true, true),
            new PathNode(null, "d", false, false),
            new PathNode(null, "f", false, false),
        ], ["a" => "a-d", "d" => "d-f"], $getRosmaroWithVisited(["obj1" => "a"]));
    }
    
    /**
     * @param String[] $idsOfNodes like ["a", "b", ...]
     * @param String[] $arrows like ["a-b", "b-e", ...]
     * @param String[] $visitedNodes like ["instanceId1" => "a", "instanceId2" => "b", "instanceId3" => "a", ...]
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
        foreach ($visitedNodes as $instanceId => $stateId) {
            $rosmaroStorage->storeFor($rosmaroId, new StateData(
                $instanceId, 
                $stateId, 
                new Context([])
            ));
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