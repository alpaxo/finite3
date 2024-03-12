<?php

declare(strict_types=1);

namespace Finite\Test\Event;

use Finite\Event\TransitionEvent;
use Finite\State\State;
use Finite\StateMachine\StateMachine;
use Finite\Transition\Transition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransitionEventTest extends TestCase
{
    protected MockObject $transition;

    protected TransitionEvent $object;

    /**
     * @throws \Finite\Exception\TransitionException
     */
    protected function setUp(): void
    {
        $this->transition = $this->getMockBuilder(Transition::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->transition
            ->expects($this->once())
            ->method('resolveProperties')
            ->with($this->isType('array'))
            ->willReturn(['returned' => 1])
        ;

        $this->object = new TransitionEvent(
            $this->getMockBuilder(State::class)->disableOriginalConstructor()->getMock(),
            $this->transition,
            $this->getMockBuilder(StateMachine::class)->disableOriginalConstructor()->getMock(),
            []
        );
    }

    public function testItResolveProperties(): void
    {
        $this->assertSame(['returned' => 1], $this->object->getProperties());
    }

    public function testPropertyGetters(): void
    {
        $this->assertSame(1, $this->object->get('returned'));
        $this->assertTrue($this->object->has('returned'));
        $this->assertNull($this->object->get('foo'));
    }
}
