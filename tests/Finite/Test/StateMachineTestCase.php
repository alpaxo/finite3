<?php

declare(strict_types=1);

namespace Finite\Test;

use Finite\State\Accessor\StateAccessorInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class StateMachineTestCase extends TestCase
{
    protected StateMachine $object;

    protected EventDispatcher $dispatcher;

    protected MockObject $accessor;

    public function setUp(): void
    {
        $this->accessor = $this->createMock(StateAccessorInterface::class);
        $this->dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->object = new StateMachine(null, $this->dispatcher, $this->accessor);
    }

    public function statesProvider(): array
    {
        return [
            [new State('s1', StateInterface::TYPE_INITIAL)],
            [new State('s2', StateInterface::TYPE_NORMAL, [], ['visible' => true])],
            ['s3'],
            [new State('s4', StateInterface::TYPE_NORMAL, [], ['visible' => true])],
            [new State('s5', StateInterface::TYPE_FINAL, [], ['visible' => false])],
        ];
    }

    public function transitionsProvider(): array
    {
        return [
            ['t12', 's1', 's2'],
            ['t23', 's2', 's3'],
            ['t34', 's3', 's4'],
            ['t45', 's4', 's5'],
        ];
    }

    protected function addStates(): void
    {
        foreach ($this->statesProvider() as $state) {
            $this->object->addState($state[0]);
        }
    }

    /**
     * @throws \Finite\Exception\TransitionException
     * @throws \Finite\Exception\StateException
     */
    protected function addTransitions(): void
    {
        foreach ($this->transitionsProvider() as $transitions) {
            $this->object->addTransition($transitions[0], $transitions[1], $transitions[2]);
        }
    }

    /**
     * @throws \Finite\Exception\NoSuchPropertyException
     * @throws \Finite\Exception\ObjectException
     * @throws \Finite\Exception\StateException
     * @throws \Finite\Exception\TransitionException
     */
    protected function initialize(): void
    {
        $this->addStates();
        $this->addTransitions();
        $this->object->setObject($this->getStatefulObjectMock());
        $this->object->initialize();
    }

    protected function getStatefulObjectMock(): MockObject
    {
        $mock = $this->createMock(StatefulInterface::class);

        $this->accessor->expects($this->at(0))
            ->method('getState')
            ->willReturn('s2')
        ;

        return $mock;
    }
}
