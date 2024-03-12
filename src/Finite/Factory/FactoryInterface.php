<?php

declare(strict_types=1);

namespace Finite\Factory;

use Finite\StateMachine\StateMachineInterface;

/**
 * The base interface for Finite's State Machine Factory.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface FactoryInterface
{
    /**
     * Returns a StateMachine instance initialized on $object.
     */
    public function get(object $object, string $graph = 'default'): StateMachineInterface;
}
