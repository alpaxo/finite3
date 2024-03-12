<?php

declare(strict_types=1);

namespace Finite\StateMachine;

use Finite\Event\FiniteEvents;
use Finite\Event\StateMachineEvent;
use Finite\Event\TransitionEvent;
use Finite\Exception;
use Finite\Exception\StateException;
use Finite\Exception\TransitionException;
use Finite\State\Accessor\PropertyPathStateAccessor;
use Finite\State\Accessor\StateAccessorInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\Transition\Transition;
use Finite\Transition\TransitionInterface;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The Finite State Machine.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class StateMachine implements StateMachineInterface
{
    /**
     * The stateful object.
     */
    protected ?object $object;

    /**
     * The available states.
     */
    protected array $states = [];

    /**
     * The available transitions.
     */
    protected array $transitions = [];

    /**
     * The current state.
     */
    protected StateInterface $currentState;

    protected EventDispatcher|EventDispatcherInterface $dispatcher;

    protected StateAccessorInterface|PropertyPathStateAccessor $stateAccessor;

    protected ?string $graph = null;

    public function __construct(
        object $object = null,
        EventDispatcherInterface $dispatcher = null,
        StateAccessorInterface $stateAccessor = null
    ) {
        $this->object = $object;
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->stateAccessor = $stateAccessor ?: new PropertyPathStateAccessor();
    }

    /**
     * {@inheritdoc}
     * @throws \Finite\Exception\NoSuchPropertyException
     * @throws \Finite\Exception\ObjectException
     * @throws \Finite\Exception\StateException
     * @throws \Finite\Exception\TransitionException
     */
    public function initialize(): void
    {
        if (null === $this->object) {
            throw new Exception\ObjectException('No object bound to the State Machine');
        }

        try {
            $initialState = $this->stateAccessor->getState($this->object);
        } catch (Exception\NoSuchPropertyException $e) {
            throw new Exception\ObjectException(
                sprintf(
                    'StateMachine can\'t be initialized because the defined property_path of object "%s" does not exist.',
                    get_class($this->object)
                ), $e->getCode(), $e
            );
        }

        if (null === $initialState) {
            $initialState = $this->findInitialState();
            $this->stateAccessor->setState($this->object, $initialState);

            $this->dispatcher->dispatch(new StateMachineEvent($this), FiniteEvents::SET_INITIAL_STATE);
        }

        $this->currentState = $this->getState($initialState);

        $this->dispatcher->dispatch(new StateMachineEvent($this), FiniteEvents::INITIALIZE);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\StateException
     * @throws \Finite\Exception\TransitionException|\Finite\Exception\NoSuchPropertyException
     */
    public function apply(string $transitionName, array $parameters = []): mixed
    {
        $transition = $this->getTransition($transitionName);
        $event = new TransitionEvent($this->getCurrentState(), $transition, $this, $parameters);
        if (!$this->can($transition, $parameters)) {
            throw new StateException(
                sprintf(
                    'The "%s" transition can not be applied to the "%s" state of object "%s" with graph "%s".',
                    $transition->getName(),
                    $this->currentState->getName(),
                    $this->getObject() ? get_class($this->getObject()) : 'undefined',
                    $this->getGraph()
                )
            );
        }

        $this->dispatchTransitionEvent($transition, $event, FiniteEvents::PRE_TRANSITION);

        $returnValue = $transition->process($this);
        $this->stateAccessor->setState($this->object, $transition->getState());
        $this->currentState = $this->getState($transition->getState());

        $this->dispatchTransitionEvent($transition, $event, FiniteEvents::POST_TRANSITION);

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     * @throws \Finite\Exception\TransitionException
     */
    public function can(TransitionInterface|string $transition, array $parameters = []): bool
    {
        $transition = $transition instanceof TransitionInterface ? $transition : $this->getTransition($transition);

        if (null !== $transition->getGuard() && !call_user_func($transition->getGuard(), $this)) {
            return false;
        }

        if (!in_array($transition->getName(), $this->getCurrentState()->getTransitions(), true)) {
            return false;
        }

        $event = new TransitionEvent($this->getCurrentState(), $transition, $this, $parameters);
        $this->dispatchTransitionEvent($transition, $event, FiniteEvents::TEST_TRANSITION);

        return !$event->isRejected();
    }

    /**
     * {@inheritdoc}
     */
    public function addState($state): void
    {
        if (!$state instanceof StateInterface) {
            $state = new State($state);
        }

        $this->states[$state->getName()] = $state;
    }

    /**
     * {@inheritdoc}
     * @throws \Finite\Exception\TransitionException|\Finite\Exception\StateException
     */
    public function addTransition(TransitionInterface|string $transition, string $initialState = null, string $finalState = null): void
    {
        if ((null === $initialState || null === $finalState) && !$transition instanceof TransitionInterface) {
            throw new InvalidArgumentException(
                'You must provide a TransitionInterface instance or the $transition, ' .
                '$initialState and $finalState parameters'
            );
        }
        // If transition isn't a TransitionInterface instance, we create one from the states date
        if (!$transition instanceof TransitionInterface) {
            try {
                $transition = $this->getTransition($transition);
            } catch (TransitionException) {
                $transition = new Transition($transition, $initialState, $finalState);
            }
        }

        $this->transitions[$transition->getName()] = $transition;

        // We add missings states to the State Machine
        try {
            $this->getState($transition->getState());
        } catch (StateException) {
            $this->addState($transition->getState());
        }

        foreach ($transition->getInitialStates() as $state) {
            try {
                $this->getState($state);
            } catch (StateException) {
                $this->addState($state);
            }

            $state = $this->getState($state);

            $state->addTransition($transition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTransition(string $name): TransitionInterface
    {
        if (!isset($this->transitions[$name])) {
            throw new TransitionException(
                sprintf(
                    'Unable to find a transition called "%s" on object "%s" with graph "%s".',
                    $name,
                    $this->getObject() ? get_class($this->getObject()) : 'undefined',
                    $this->getGraph()
                )
            );
        }

        return $this->transitions[$name];
    }

    /**
     * {@inheritdoc}
     * @throws \Finite\Exception\StateException
     */
    public function getState($name): StateInterface
    {
        $name = (string)$name;
        
        if (!isset($this->states[$name])) {
            throw new StateException(
                sprintf(
                    'Unable to find a state called "%s" on object "%s" with graph "%s".',
                    $name,
                    $this->getObject() ? get_class($this->getObject()) : 'undefined',
                    $this->getGraph()
                )
            );
        }

        return $this->states[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitions(): array
    {
        return array_keys($this->transitions);
    }

    /**
     * {@inheritdoc}
     */
    public function getStates(): array
    {
        return array_keys($this->states);
    }

    public function setObject($object): void
    {
        $this->object = $object;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }

    public function getCurrentState(): StateInterface
    {
        return $this->currentState;
    }

    /**
     * Find and return the Initial state if exists.
     *
     * @return string
     *
     * @throws Exception\StateException
     */
    protected function findInitialState(): string
    {
        foreach ($this->states as $state) {
            if (StateInterface::TYPE_INITIAL === $state->getType()) {
                return $state->getName();
            }
        }

        throw new StateException(
            sprintf(
                'No initial state found on object "%s" with graph "%s".',
                $this->getObject() ? get_class($this->getObject()) : 'undefined',
                $this->getGraph()
            )
        );
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function setStateAccessor(StateAccessorInterface $stateAccessor): void
    {
        $this->stateAccessor = $stateAccessor;
    }

    public function getStateAccessor(): ?StateAccessorInterface
    {
        return $this->stateAccessor;
    }

    public function hasStateAccessor(): bool
    {
        return true;
    }

    public function setGraph(string $graph): void
    {
        $this->graph = $graph;
    }

    public function getGraph(): ?string
    {
        return $this->graph;
    }

    /**
     * {@inheritDoc}
     */
    public function findStateWithProperty(string $property, mixed $value = null): array
    {
        return array_keys(
            array_map(
                static function (State $state) {
                    return $state->getName();
                },
                array_filter(
                    $this->states,
                    static function (State $state) use ($property, $value) {
                        if (!$state->has($property)) {
                            return false;
                        }

                        if (null !== $value && $state->get($property) !== $value) {
                            return false;
                        }

                        return true;
                    }
                )
            )
        );
    }

    /**
     * Dispatches event for the transition
     */
    private function dispatchTransitionEvent(TransitionInterface $transition, TransitionEvent $event, string $transitionState): void
    {
        $this->dispatcher->dispatch($event, $transitionState);
        $this->dispatcher->dispatch($event, $transitionState . '.' . $transition->getName());

        if (null !== $this->getGraph()) {
            $this->dispatcher->dispatch($event, $transitionState . '.' . $this->getGraph() . '.' . $transition->getName());
        }
    }
}
