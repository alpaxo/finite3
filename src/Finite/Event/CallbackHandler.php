<?php

declare(strict_types=1);

namespace Finite\Event;

use Finite\Event\Callback\Callback;
use Finite\Event\Callback\CallbackBuilder;
use Finite\StateMachine\StateMachineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage callback-to-event bindings by trigger spec definition.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class CallbackHandler
{
    /**
     * @deprecated To be removed in 2.0
     */
    public const ALL = 'all';

    protected EventDispatcherInterface $dispatcher;

    protected OptionsResolver $specResolver;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->specResolver = new OptionsResolver();
        $this->specResolver->setDefaults(
            [
                'on' => self::ALL,
                'from' => self::ALL,
                'to' => self::ALL,
            ]
        );

        $this->specResolver->setAllowedTypes('on', ['string', 'array']);
        $this->specResolver->setAllowedTypes('from', ['string', 'array']);
        $this->specResolver->setAllowedTypes('to', ['string', 'array']);

        $toArrayNormalizer = function (Options $options, $value) {
            return (array)$value;
        };

        $this->specResolver->setNormalizer('on', $toArrayNormalizer);
        $this->specResolver->setNormalizer('from', $toArrayNormalizer);
        $this->specResolver->setNormalizer('to', $toArrayNormalizer);
    }

    /**
     * @return \Finite\Event\CallbackHandler
     */
    public function addBefore(Callback|StateMachineInterface $smOrCallback, callable $callback = null, array $spec = [])
    {
        $this->add($smOrCallback, FiniteEvents::PRE_TRANSITION, $callback, $spec);

        return $this;
    }

    /**
     * @return \Finite\Event\CallbackHandler
     */
    public function addAfter(Callback|StateMachineInterface $smOrCallback, callable $callback = null, array $spec = [])
    {
        $this->add($smOrCallback, FiniteEvents::POST_TRANSITION, $callback, $spec);

        return $this;
    }

    /**
     * @return \Finite\Event\CallbackHandler
     */
    protected function add(Callback|StateMachineInterface $smOrCallback, string $event, callable $callable = null, array $specs = [])
    {
        if ($smOrCallback instanceof Callback) {
            $this->dispatcher->addListener($event, $smOrCallback);

            return $this;
        }

        trigger_error(
            'Use of CallbackHandler::add without a Callback instance is deprecated and will be removed in 2.0',
            E_USER_DEPRECATED
        );

        $specs = $this->specResolver->resolve($specs);
        $callback = CallbackBuilder::create($smOrCallback, $specs['from'], $specs['to'], $specs['on'], $callable)->getCallback();

        $this->dispatcher->addListener($event, $callback);

        return $this;
    }
}
