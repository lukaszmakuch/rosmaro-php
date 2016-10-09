<?php

namespace lukaszmakuch\Rosmaro;

class PathReaderTest extends \PHPUnit_Framework_TestCase
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
            ["id" => "obj1", "type" => "a", "visited" => true, "current" => false],
            ["id" => "obj2", "type" => "b", "visited" => true, "current" => false],
            ["id" => "obj3", "type" => "a", "visited" => true, "current" => true],
            ["id" => null, "type" => "b", "visited" => false, "current" => false],
            ["id" => null, "type" => "c", "visited" => false, "current" => false],
        ], [], $getRosmaroWithVisited(["obj1" => "a", "obj2" => "b", "obj3" => "a"]));

        $this->assertFlatRepresentation([
            ["id" => "obj1", "type" => "a", "visited" => true, "current" => false],
            ["id" => "obj2", "type" => "d", "visited" => true, "current" => true],
            ["id" => null, "type" => "e", "visited" => false, "current" => false],
        ], [], $getRosmaroWithVisited(["obj1" => "a", "obj2" => "d"]));

        $this->assertFlatRepresentation([
            ["id" => "obj1", "type" => "a", "visited" => true, "current" => true],
            ["id" => null, "type" => "d", "visited" => false, "current" => false],
            ["id" => null, "type" => "f", "visited" => false, "current" => false],
        ], ["a" => "a-d", "d" => "d-f"], $getRosmaroWithVisited(["obj1" => "a"]));
    }

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

        $rosmaroStorage = new InMemoryStorage();
        foreach ($visitedNodes as $id => $type) {
            $rosmaroStorage->storeFor($rosmaroId, [
                'id' => $id,
                'type' => $type,
                'context' => new Context([])
            ]);
        }

        return new Rosmaro(
            $rosmaroId,
            reset($idsOfNodes),
            $transitions,
            $prototypes,
            $rosmaroStorage
        );
    }

    private function assertFlatRepresentation($expectedRepresentation, $preferredArrows, $rosmaro)
    {
        $actualRepresentation = (new PathReader($preferredArrows))->getNodesOf($rosmaro);
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
