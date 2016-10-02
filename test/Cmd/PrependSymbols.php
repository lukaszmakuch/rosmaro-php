<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro\Cmd;

class PrependSymbols
{
    public $howMany;
    public function __construct($howMany) { $this->howMany = $howMany; }
}