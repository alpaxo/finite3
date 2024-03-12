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
     */
    public function apply(string $transitionName, array $parameters = []): mixed;

    /**
     * Returns if the transition is applicable.
     */
    public function can(TransitionInterface|string $transition, array $parameters = []): bool;

    /**
     * Add a state
     */
    public function addState(string|StateInterface $state);

    /**
     * @throws \InvalidArgumentException
     */
    public function addTransition(TransitionInterface|string $transition, mixed $initialState = null, mixed $finalState = null);

    /**
     * Returns a transition by its name.
     *
     * @throws \Finite\Exception\TransitionException
     */
    public function getTransition(string $name): TransitionInterface;

    /**
     * @throws \Finite\Exception\TransitionException
     */
    public function getState(string|\Stringable|int $name): StateInterface;

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

    public function setObject(object $object);

    public function getObject(): ?object;

    public function getCurrentState(): StateInterface;

    public function setDispatcher(EventDispatcherInterface $dispatcher): void;

    public function getDispatcher(): EventDispatcherInterface;

    public function setStateAccessor(StateAccessorInterface $stateAccessor);

    public function hasStateAccessor(): bool;

    public function setGraph(string $graph);

    public function getGraph(): ?string;

    /**
     * Find a state which have a given property, with an optional given value.
     * It is useful for looking for objects having a given property in database for example.
     */
    public function findStateWithProperty(string $property, mixed $value = null): array;
}
