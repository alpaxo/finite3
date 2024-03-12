<?php

declare(strict_types=1);

namespace Finite;

use Finite\Factory\FactoryInterface;
use Finite\StateMachine\StateMachineInterface;

/**
 * The Finite context.
 * It provides easy ways to deal with Stateful objects, and factory.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class Context
{
    protected FactoryInterface $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function getState(object $object, string $graph = 'default'): string
    {
        return $this->getStateMachine($object, $graph)->getCurrentState()->getName();
    }

    /**
     * @return array<string>
     * @throws \Finite\Exception\TransitionException
     */
    public function getTransitions(object $object, string $graph = 'default', bool $asObject = false): array
    {
        if (!$asObject) {
            return $this->getStateMachine($object, $graph)->getCurrentState()->getTransitions();
        }

        $stateMachine = $this->getStateMachine($object, $graph);

        return array_map(
            static function ($transition) use ($stateMachine) {
                return $stateMachine->getTransition($transition);
            },
            $stateMachine->getCurrentState()->getTransitions()
        );
    }

    /**
     * @return array<string>
     */
    public function getProperties(object $object, string $graph = 'default'): array
    {
        return $this->getStateMachine($object, $graph)->getCurrentState()->getProperties();
    }

    public function hasProperty(object $object, string $property, string $graph = 'default'): bool
    {
        return $this->getStateMachine($object, $graph)->getCurrentState()->has($property);
    }

    public function getStateMachine(object $object, string $graph = 'default'): StateMachineInterface
    {
        return $this->getFactory()->get($object, $graph);
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }
}
