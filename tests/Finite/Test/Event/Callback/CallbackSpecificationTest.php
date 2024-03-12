<?php

declare(strict_types=1);

namespace Finite\Test\Event\Callback;

use Finite\Event\Callback\CallbackSpecification;
use Finite\Event\TransitionEvent;
use Finite\State\State;
use Finite\StateMachine\StateMachine;
use Finite\Transition\Transition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class CallbackSpecificationTest extends TestCase
{
    private MockObject $stateMachine;

    protected function setUp(): void
    {
        $this->stateMachine = $this->createMock(StateMachine::class);
    }

    /**
     * @throws \Finite\Exception\TransitionException
     */
    public function testItIsSatisfiedByFrom(): void
    {
        $spec = new CallbackSpecification($this->stateMachine, ['s1', 's2'], [], []);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));

        $spec = new CallbackSpecification($this->stateMachine, ['-s3'], [], []);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));
    }

    /**
     * @throws \Finite\Exception\TransitionException
     */
    public function testItIsSatisfiedByTo(): void
    {
        $spec = new CallbackSpecification($this->stateMachine, [], ['s2', 's3'], []);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));

        $spec = new CallbackSpecification($this->stateMachine, [], ['-s4'], []);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));
    }

    /**
     * @throws \Finite\Exception\TransitionException
     */
    public function testItIsSatisfiedByOn(): void
    {
        $spec = new CallbackSpecification($this->stateMachine, [], [], ['t12', 't23']);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));

        $spec = new CallbackSpecification($this->stateMachine, [], [], ['-t34']);

        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s1', 't12', 's2')));
        $this->assertTrue($spec->isSatisfiedBy($this->getTransitionEvent('s2', 't23', 's3')));
        $this->assertFalse($spec->isSatisfiedBy($this->getTransitionEvent('s3', 't34', 's4')));
    }

    /**
     * @throws \Finite\Exception\TransitionException
     */
    private function getTransitionEvent(string $fromState, string $transition, string $toState): TransitionEvent
    {
        return new TransitionEvent(
            new State($fromState),
            new Transition($transition, [$fromState], $toState),
            $this->stateMachine
        );
    }
}
