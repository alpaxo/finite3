<?php

declare(strict_types=1);

namespace Finite\Loader;

use Finite\StateMachine\StateMachineInterface;

/**
 * State & Transitions Loader interface.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface LoaderInterface
{
    /**
     * Loads a state machine.
     */
    public function load(StateMachineInterface $stateMachine): void;

    /**
     * Returns if this loader supports $object for $graph.
     */
    public function supports(object $object, string $graph = 'default'): bool;
}
