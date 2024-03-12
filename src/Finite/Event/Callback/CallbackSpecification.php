<?php

namespace Finite\Event\Callback;

use Finite\Event\CallbackHandler;
use Finite\Event\TransitionEvent;
use Finite\StateMachine\StateMachineInterface;

/**
 * Concrete implementation of CallbackSpecification.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class CallbackSpecification implements CallbackSpecificationInterface
{
    private array $specs = [];

    private StateMachineInterface $stateMachine;

    public function __construct(StateMachineInterface $sm, array $from, array $to, array $on)
    {
        $this->stateMachine = $sm;

        $isExclusion = static function ($str) {
            return str_starts_with($str, '-');
        };

        $removeDash = static function ($str) {
            return substr($str, 1);
        };

        foreach (compact('from', 'to', 'on') as $clause => $arg) {
            $excludedClause = 'excluded_'.$clause;

            $this->specs[$excludedClause] = array_filter($arg, $isExclusion);
            $this->specs[$clause] = array_diff($arg, $this->specs[$excludedClause]);
            $this->specs[$excludedClause] = array_map($removeDash, $this->specs[$excludedClause]);

            // For compatibility with old CallbackHandler.
            // To be removed in 2.0
            if (in_array(CallbackHandler::ALL, $this->specs[$clause], true)) {
                $this->specs[$clause] = array();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(TransitionEvent $event): bool
    {
        return
            $event->getStateMachine() === $this->stateMachine &&
            $this->supportsClause('from', $event->getInitialState()->getName()) &&
            $this->supportsClause('to', $event->getTransition()->getState()) &&
            $this->supportsClause('on', $event->getTransition()->getName());
    }

    private function supportsClause(string $clause, string $property): bool
    {
        $excludedClause = 'excluded_'.$clause;

        return
            (0 === count($this->specs[$clause]) || in_array($property, $this->specs[$clause], true)) &&
            (0 === count($this->specs[$excludedClause]) || !in_array($property, $this->specs[$excludedClause], true));
    }
}
