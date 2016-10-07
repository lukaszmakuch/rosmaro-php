<?php

/**
 * This file is part of the Rosmaro library.
 *
 * @author Łukasz Makuch <kontakt@lukaszmakuch.pl>
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace lukaszmakuch\Rosmaro;

use lukaszmakuch\Rosmaro\Exception\VisitationFailed;

interface StateVisitor
{
    /**
     * @param State $s
     * @return mixed
     * @throws VisitationFailed
     */
    public function visit(State $s);
}