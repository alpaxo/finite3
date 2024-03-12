<?php

namespace Finite\State;

use Finite\PropertiesAwareInterface;
use Finite\Transition\TransitionInterface;

/**
 * The base State Interface.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface StateInterface extends PropertiesAwareInterface
{
    public const
        TYPE_INITIAL = 'initial',
        TYPE_NORMAL = 'normal',
        TYPE_FINAL = 'final';

    /**
     * Returns the state name.
     */
    public function getName(): string;

    /**
     * Returns if this state is the initial state.
     */
    public function isInitial(): bool;

    /**
     * Returns if this state is the final state.
     */
    public function isFinal(): bool;

    /**
     * Returns if this state is a normal state.
     */
    public function isNormal(): bool;

    /**
     * Returns the state type.
     */
    public function getType(): string;

    /**
     * Add transition to the state
     */
    public function addTransition(TransitionInterface|string $transition): void;

    /**
     * Returns the available transitions.
     */
    public function getTransitions(): array;

    /**
     * Returns if this state can run $transition.
     *
     * @deprecated Deprecated since version 1.0.0-BETA2. Use {@link StateMachine::can($transition)} instead.
     */
    public function can(TransitionInterface|string $transition): bool;
}
