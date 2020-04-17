<?php

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
     *
     * @return array
     */
    public function getInitialStates(): array;

    /**
     * Returns the state resulting of this transition.
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Process the transition.
     *
     * @param \Finite\StateMachine\StateMachineInterface $stateMachine
     *
     * @return mixed
     */
    public function process(StateMachineInterface $stateMachine);

    /**
     * Returns the name of the transition.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the closure. If closure execution returns false, transition cannot be applied.
     *
     * @return callable|null
     */
    public function getGuard(): ?callable;
}
