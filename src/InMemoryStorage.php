<?php

namespace lukaszmakuch\Rosmaro;

class InMemoryStorage
{
    private $stackByRosmaroId = [];

    public function isEmptyFor($rosmaroId)
    {
        return !isset($this->stackByRosmaroId[$rosmaroId]);
    }

    public function getAllFor(
        $rosmaroId,
        $stateDataToStoreIfNothingFound
    ) {
        if (!isset($this->stackByRosmaroId[$rosmaroId])) {
            $this->stackByRosmaroId[$rosmaroId] = [$stateDataToStoreIfNothingFound];
        }

        return $this->stackByRosmaroId[$rosmaroId];
    }

    public function getRecentFor(
        $rosmaroId,
        $stateDataToStoreIfNothingFound
    ) {
        if (!isset($this->stackByRosmaroId[$rosmaroId])) {
            $this->stackByRosmaroId[$rosmaroId] = [$stateDataToStoreIfNothingFound];
        }

        return end($this->stackByRosmaroId[$rosmaroId]);
    }

    public function removeAllDataFor($rosmaroId)
    {
        unset($this->stackByRosmaroId[$rosmaroId]);
    }

    public function revertFor(
        $rosmaroId,
        $stateId
    ) {
        $newStack = [];
        foreach ($this->stackByRosmaroId[$rosmaroId] as $stateFromOldStack) {
            $newStack[] = $stateFromOldStack;
            if ($stateFromOldStack['id'] == $stateId) {
                $this->stackByRosmaroId[$rosmaroId] = $newStack;
                break;
            }
        }
    }

    public function storeFor(
        $rosmaroId,
        $stateData
    ) {
        if (!isset($this->stackByRosmaroId[$rosmaroId])) {
            $this->stackByRosmaroId[$rosmaroId] = [];
        }

        $this->stackByRosmaroId[$rosmaroId][] = $stateData;
    }
}
