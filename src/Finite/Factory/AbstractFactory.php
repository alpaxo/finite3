<?php

declare(strict_types=1);

namespace Finite\Factory;

use Finite\Loader\LoaderInterface;
use Finite\StateMachine\StateMachineInterface;

/**
 * The abstract base class for state machine factories.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
abstract class AbstractFactory implements FactoryInterface
{
    protected array $stateMachines = [];

    /**
     * @var LoaderInterface[]
     */
    protected array $loaders = [];

    /**
     * {@inheritdoc}
     */
    public function get(object $object, string $graph = 'default'): StateMachineInterface
    {
        $hash = spl_object_hash($object) . '.' . $graph;
        if (!isset($this->stateMachines[$hash])) {
            $stateMachine = $this->createStateMachine();
            if (null !== ($loader = $this->getLoader($object, $graph))) {
                $loader->load($stateMachine);
            }
            $stateMachine->setObject($object);
            $stateMachine->initialize();

            $this->stateMachines[$hash] = $stateMachine;
        }

        return $this->stateMachines[$hash];
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    protected function getLoader(object $object, string $graph): ?LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($object, $graph)) {
                return $loader;
            }
        }

        return null;
    }

    /**
     * Creates an instance of StateMachine.
     */
    abstract protected function createStateMachine(): StateMachineInterface;
}
