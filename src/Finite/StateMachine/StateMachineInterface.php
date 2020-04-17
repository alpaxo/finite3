<?php

namespace Finite\StateMachine;

use Finite\State\Accessor\StateAccessorInterface;
use Finite\State\StateInterface;
use Finite\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The Finite State Machine base Interface.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface StateMachineInterface
{
    /**
     * Initialize the State Machine current state.
     */
    public function initialize();

    /**
     * Apply a transition.
     *
     * @param string $transitionName
     * @param array  $parameters
     *
     * @return mixed
     */
    public function apply($transitionName, array $parameters = []);

    /**
     * Returns if the transition is applicable.
     *
     * @param string|TransitionInterface $transition
     * @param array                      $parameters
     *
     * @return bool
     */
    public function can($transition, array $parameters = []): bool;

    /**
     * @param string|StateInterface $state
     */
    public function addState($state);

    /**
     * @param string|TransitionInterface $transition
     * @param string|null                $initialState
     * @param string|null                $finalState
     *
     * @throws \InvalidArgumentException
     */
    public function addTransition($transition, $initialState = null, $finalState = null);

    /**
     * Returns a transition by its name.
     *
     * @param string $name
     *
     * @return TransitionInterface
     *
     * @throws \Finite\Exception\TransitionException
     */
    public function getTransition($name): TransitionInterface;

    /**
     * @param string $name
     *
     * @return StateInterface
     *
     * @throws \Finite\Exception\TransitionException
     */
    public function getState($name): StateInterface;

    /**
     * Returns an array containing all the transitions names.
     *
     * @return array<string>
     */
    public function getTransitions(): array;

    /**
     * Returns an array containing all the states names.
     *
     * @return array<string>
     */
    public function getStates(): array;

    /**
     * @param object $object
     */
    public function setObject($object);

    /**
     * @return object
     */
    public function getObject();

    /**
     * @return StateInterface
     */
    public function getCurrentState(): StateInterface;

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface;

    /**
     * @param StateAccessorInterface $stateAccessor
     */
    public function setStateAccessor(StateAccessorInterface $stateAccessor);

    /**
     * @return bool
     */
    public function hasStateAccessor(): bool;

    /**
     * @param string $graph
     */
    public function setGraph($graph);

    /**
     * @return string|null
     */
    public function getGraph(): ?string;

    /**
     * Find a state which have a given property, with an optional given value.
     * It is useful for looking for objects having a given property in database for example.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return array
     */
    public function findStateWithProperty($property, $value = null): array;
}
