<?php

declare(strict_types=1);

namespace Finite\Factory;

use Finite\Exception\FactoryException;
use Finite\StateMachine\StateMachineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A concrete implementation of State Machine Factory using the sf2 DIC.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class SymfonyDependencyInjectionFactory extends AbstractFactory
{
    protected ContainerInterface $container;

    protected string $key;

    /**
     * @throws \Finite\Exception\FactoryException
     */
    public function __construct(ContainerInterface $container, string $key)
    {
        $this->container = $container;
        $this->key = $key;

        if (!$container->has($key)) {
            throw new FactoryException(
                sprintf(
                    'You must define the "%s" service as your StateMachine definition',
                    $key
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function createStateMachine(): StateMachineInterface
    {
        return $this->container->get($this->key);
    }
}
