<?php

namespace Finite\Loader;

use Finite\Event\Callback\CallbackBuilderFactory;
use Finite\Event\Callback\CallbackBuilderFactoryInterface;
use Finite\Event\CallbackHandler;
use Finite\State\Accessor\PropertyPathStateAccessor;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachineInterface;
use Finite\Transition\Transition;
use ReflectionClass;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Loads a StateMachine from an array.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class ArrayLoader implements LoaderInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var CallbackHandler
     */
    private $callbackHandler;

    /**
     * @var CallbackBuilderFactoryInterface
     */
    private $callbackBuilderFactory;

    /**
     * @param array                           $config
     * @param CallbackHandler                 $handler
     * @param CallbackBuilderFactoryInterface $callbackBuilderFactory
     */
    public function __construct(array $config, CallbackHandler $handler = null, CallbackBuilderFactoryInterface $callbackBuilderFactory = null)
    {
        $this->callbackHandler = $handler;
        $this->callbackBuilderFactory = $callbackBuilderFactory;
        $this->config = array_merge(
            [
                'class' => '',
                'graph' => 'default',
                'property_path' => 'finiteState',
                'states' => [],
                'transitions' => [],
            ],
            $config
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load(StateMachineInterface $stateMachine): void
    {
        if (null === $this->callbackHandler) {
            $this->callbackHandler = new CallbackHandler($stateMachine->getDispatcher());
        }

        if (null === $this->callbackBuilderFactory) {
            $this->callbackBuilderFactory = new CallbackBuilderFactory();
        }

        if (!$stateMachine->hasStateAccessor()) {
            $stateMachine->setStateAccessor(new PropertyPathStateAccessor($this->config['property_path']));
        }

        $stateMachine->setGraph($this->config['graph']);

        $this->loadStates($stateMachine);
        $this->loadTransitions($stateMachine);
        $this->loadCallbacks($stateMachine);
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function supports($object, $graph = 'default'): bool
    {
        $reflection = new ReflectionClass($this->config['class']);

        return $reflection->isInstance($object) && $graph === $this->config['graph'];
    }

    /**
     * @param \Finite\StateMachine\StateMachineInterface $stateMachine
     */
    private function loadStates(StateMachineInterface $stateMachine): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['type' => StateInterface::TYPE_NORMAL, 'properties' => []]);
        $resolver->setAllowedValues(
            'type',
            [
                StateInterface::TYPE_INITIAL,
                StateInterface::TYPE_NORMAL,
                StateInterface::TYPE_FINAL,
            ]
        );

        foreach ($this->config['states'] as $state => $config) {
            $config = $resolver->resolve($config);
            $stateMachine->addState(new State($state, $config['type'], [], $config['properties']));
        }
    }

    /**
     * @param \Finite\StateMachine\StateMachineInterface $stateMachine
     * @noinspection PhpUnusedParameterInspection
     */
    private function loadTransitions(StateMachineInterface $stateMachine): void
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['from', 'to']);
        $resolver->setDefaults(['guard' => null, 'configure_properties' => null, 'properties' => []]);

        $resolver->setAllowedTypes('configure_properties', ['null', 'callable']);

        $resolver->setNormalizer(
            'from',
            static function (Options $options, $v) {
                return (array)$v;
            }
        );
        $resolver->setNormalizer(
            'guard',
            static function (Options $options, $v) {
                return $v ?? null;
            }
        );
        $resolver->setNormalizer(
            'configure_properties',
            static function (Options $options, $v) {
                $resolver = new OptionsResolver();

                $resolver->setDefaults($options['properties']);

                if (is_callable($v)) {
                    $v($resolver);
                }

                return $resolver;
            }
        );

        foreach ($this->config['transitions'] as $transition => $config) {
            $config = $resolver->resolve($config);
            $stateMachine->addTransition(
                new Transition(
                    $transition,
                    $config['from'],
                    $config['to'],
                    $config['guard'],
                    $config['configure_properties']
                )
            );
        }
    }

    /**
     * @param \Finite\StateMachine\StateMachineInterface $stateMachine
     */
    private function loadCallbacks(StateMachineInterface $stateMachine): void
    {
        if (!isset($this->config['callbacks'])) {
            return;
        }

        foreach (['before', 'after'] as $position) {
            $this->loadCallbacksFor($position, $stateMachine);
        }
    }

    private function loadCallbacksFor($position, $stateMachine): void
    {
        if (!isset($this->config['callbacks'][$position])) {
            return;
        }

        $method = 'add' . ucfirst($position);
        $resolver = $this->getCallbacksResolver();
        foreach ($this->config['callbacks'][$position] as $specs) {
            $specs = $resolver->resolve($specs);

            $callback = $this->callbackBuilderFactory->createBuilder($stateMachine)
                ->setFrom($specs['from'])
                ->setTo($specs['to'])
                ->setOn($specs['on'])
                ->setCallable($specs['do'])
                ->getCallback()
            ;

            $this->callbackHandler->$method($callback);
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function getCallbacksResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(
            [
                'on' => [],
                'from' => [],
                'to' => [],
            ]
        );

        $resolver->setRequired(['do']);

        $resolver->setAllowedTypes('on', ['string', 'array']);
        $resolver->setAllowedTypes('from', ['string', 'array']);
        $resolver->setAllowedTypes('to', ['string', 'array']);

        $toArrayNormalizer = static function (Options $options, $value) {
            return (array)$value;
        };
        $resolver->setNormalizer('on', $toArrayNormalizer);
        $resolver->setNormalizer('from', $toArrayNormalizer);
        $resolver->setNormalizer('to', $toArrayNormalizer);

        return $resolver;
    }
}
