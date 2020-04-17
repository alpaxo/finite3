<?php

namespace Finite\State;

use Finite\PropertiesAwareInterface;

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
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns if this state is the initial state.
     *
     * @return bool
     */
    public function isInitial(): bool;

    /**
     * Returns if this state is the final state.
     *
     * @return bool
     */
    public function isFinal(): bool;

    /**
     * Returns if this state is a normal state (!($this->isInitial() || $this->isFinal()).
     *
     * @return bool
     */
    public function isNormal(): bool;

    /**
     * Returns the state type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns the available transitions.
     *
     * @return array
     */
    public function getTransitions();

    /**
     * Returns if this state can run $transition.
     *
     * @param string|\Finite\Transition\TransitionInterface $transition
     *
     * @return bool
     *
     * @deprecated Deprecated since version 1.0.0-BETA2. Use {@link StateMachine::can($transition)} instead.
     */
    public function can($transition);
}
