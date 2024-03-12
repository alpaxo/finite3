<?php

declare(strict_types=1);

namespace Finite\Test\Event\Callback;

use Finite\Event\Callback\CallbackBuilder;
use Finite\Event\Callback\CallbackSpecification;
use Finite\StateMachine\StateMachine;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class CallbackBuilderTest extends TestCase
{
    public function testItBuildsCallback(): void
    {
        $stateMachine = $this
            ->getMockBuilder(StateMachine::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $callableMock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['call'])
            ->getMock()
        ;

        $callback = CallbackBuilder::create($stateMachine, [$callableMock, 'call'])
            ->setFrom(['s1'])
            ->addFrom('s2')
            ->setTo(['s2'])
            ->addTo('s3')
            ->setOn(['t12'])
            ->addOn('t23')
            ->getCallback()
        ;

        $this->assertInstanceOf(CallbackSpecification::class, $callback->getSpecification());
    }
}
