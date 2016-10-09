<?php

namespace lukaszmakuch\Rosmaro;

class InMemoryStorage
{
    private $stackByRosmaroId = [];

    public function getAllStatesDataFor(
        $rosmaroId,
        $stateDataToStoreIfNothingFound
    ) {
        $this->storeIfEmptyStackFor($rosmaroId, $stateDataToStoreIfNothingFound);
        return array_values($this->stackByRosmaroId[$rosmaroId]);
    }

    public function getCurrentStateDataFor(
        $rosmaroId,
        $stateDataToStoreIfNothingFound
    ) {
        $allStatesData = $this->getAllStatesDataFor($rosmaroId, $stateDataToStoreIfNothingFound);
        return end($allStatesData);
    }

    public function removeAllDataFor($rosmaroId)
    {
        unset($this->stackByRosmaroId[$rosmaroId]);
    }

    public function storeFor($rosmaroId, $stateData)
    {
        $this->initEmptyStackIfNotExistFor($rosmaroId);
        $this->stackByRosmaroId[$rosmaroId][] = $stateData;
    }

    public function revertFor($rosmaroId, $stateId)
    {
        $newStack = [];
        foreach ($this->stackByRosmaroId[$rosmaroId] as $stateFromOldStack) {
            $newStack[] = $stateFromOldStack;
            if ($stateFromOldStack['id'] == $stateId) {
                $this->stackByRosmaroId[$rosmaroId] = $newStack;
                break;
            }
        }
    }

    //exists for testing purposes
    public function isEmptyFor($rosmaroId)
    {
        return !isset($this->stackByRosmaroId[$rosmaroId]);
    }

    private function storeIfEmptyStackFor($rosmaroId, $firstStateData)
    {
        $this->initEmptyStackIfNotExistFor($rosmaroId);
        if (empty($this->stackByRosmaroId[$rosmaroId])) {
            $this->stackByRosmaroId[$rosmaroId] = [$firstStateData];
        }
    }

    private function initEmptyStackIfNotExistFor($rosmaroId)
    {
        if (!isset($this->stackByRosmaroId[$rosmaroId])) {
            $this->stackByRosmaroId[$rosmaroId] = [];
        }
    }
}
