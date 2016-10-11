# Rosmaro
[![travis](https://travis-ci.org/lukaszmakuch/rosmaro.svg)](https://travis-ci.org/lukaszmakuch/rosmaro)

State machine based, persistent objects.

## What is it for?
If in your model there are many conditional statements describing what actions should be available and what should they do depending on its current state, Rosmaro can help. By putting behavior related to each state in a separated object, it's possible to describe state changes as a nondeterministic state machine. A single state runs within some context given by the previous state and may cause a transition by picking one of possible arrows. The current state is persisted and may be reversed to any previous state.

## Simple example
```php
<?php
use lukaszmakuch\Rosmaro\Rosmaro;
use lukaszmakuch\Rosmaro\State;

//When it's hit too many times, it follows the "hit_too_many_times" arrow.
class LockedBox extends State {
    function hit() {
        $howManyTimes = (int)$this->context->hit_counter + 1;
        $this->transition(
            $howManyTimes > 2 ? "hit_too_many_times" : "still_fine",
            $this->context->copyWith(['hit_counter' => $howManyTimes])
        );
    }
}

//Exposes its things. Cannot be hit more.
class BrokenBox extends State {
    public $things = ["all", "previously", "hidden", "goodies"];
}

//composed box model
$box = new Rosmaro(
    //its unique identifier
    'box',
    //the initial state
    'locked',
    [
        //two possible transitions from the locked state
        'locked' => [
            //if it hasn't been hit too many times,
            //it doesn't change its state
            'still_fine' => 'locked',
            //hitting too many times makes it broken
            'hit_too_many_times' => 'broken'
        ],
        //there's no way to escape the broken state
        'broken' => []
    ],
    //prototypes of states
    [
        'locked' => new LockedBox,
        'broken' => new BrokenBox
    ],
    //where all the state data is stored
    new InMemoryStorage
);

//working with the model
$box->hit();
//it's still locked
$box->hit();
//this finally breaks it
$box->hit();
//now its things are exposed publicly
print_r($box->things);
```

## Methods and properties
### $rosmaro->transtion($arrow, $context)
Causes a transition to the head of the given arrow. The new state runs within the given context.

### $rosmaro->revertToPreviousState()
Reverts the model to its previous state.

### $rosmaro->revertTo($stateId)
Reverts the model to the state with the given id.

### $rosmaro->remove()
Removes all data associated with this model.

### $rosmaro->intId
An integer (crc32) id of this model. Changes only when a transition occurs.

### $rosmaro->history
An array with identifiers and types of all states of this model.
```php
[
    ["id" => "lkj", "type" => "locked"],
    ["id" => "hgf", "type" => "locked"],
    ["id" => "dsa", "type" => "broken"],
]
```

### $rosmaro->graph
A graph with all states. Provides types of states (for example: "locked", "broken") and information which one is the current one. For more details, please check the unit test.

## PathReader
Provides a path containing all of the previous states, the current state, and the preferred path from the current state till some leaf node.

```php
$reader = new PathReader(['stateA' => 'arrow2', 'stateC' => 'arrow3']);
$pathNodes = $reader->getNodesOf($someRosmaro);
/*
[
    ["id" => "x", "type" => "stateA", "visited" => true, "current" => false],
    ["id" => "y", "type" => "stateB", "visited" => true, "current" => true],
    ["id" => "z", "type" => "stateC", "visited" => false, "current" => false]
]
*/
```

For more details, please check unit tests.

## How to install
```
$ composer require lukaszmakuch/rosmaro
```
