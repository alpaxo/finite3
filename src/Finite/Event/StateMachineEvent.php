<?php

declare(strict_types=1);

namespace Finite\Event;

use Finite\StateMachine\StateMachine;
use Finite\StateMachine\StateMachineInterface;
use Symfony\Component\EventDispatcher\GenericEvent as Event;

/**
 * The event object which is thrown on state machine actions.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class StateMachineEvent extends Event
{
    protected StateMachine $stateMachine;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;

        parent::__construct();
    }

    public function getStateMachine(): StateMachineInterface
    {
        return $this->stateMachine;
    }
}
