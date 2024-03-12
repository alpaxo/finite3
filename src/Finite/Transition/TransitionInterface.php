<?php

declare(strict_types=1);

namespace Finite\Transition;

use Finite\StateMachine\StateMachineInterface;

/**
 * The base Transition interface.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface TransitionInterface
{
    /**
     * Returns the array of states that supports this transition.
     */
    public function getInitialStates(): array;

    /**
     * Returns the state resulting of this transition.
     */
    public function getState(): string;

    /**
     * Process the transition.
     */
    public function process(StateMachineInterface $stateMachine): mixed;

    /**
     * Returns the name of the transition.
     */
    public function getName(): string;

    /**
     * Returns the closure. If closure execution returns false, transition cannot be applied.
     */
    public function getGuard(): ?callable;
}
