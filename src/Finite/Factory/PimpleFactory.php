<?php

namespace Finite\Factory;

use Finite\StateMachine\StateMachineInterface;
use Pimple\Container;

/**
 * A concrete implementation of State Machine Factory using Pimple.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class PimpleFactory extends AbstractFactory
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $id;

    /**
     * @param \Pimple\Container $container
     * @param string            $id
     */
    public function __construct(Container $container, $id)
    {
        $this->container = $container;
        $this->id = $id;

        // this needed to bypass pimple service caching
        $container->factory(
            $container->raw($id)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createStateMachine(): StateMachineInterface
    {
        return $this->container[$this->id];
    }
}
