<?php

declare(strict_types=1);

namespace Finite\Event\Callback;

use Finite\Event\TransitionEvent;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class Callback implements CallbackInterface
{
    private CallbackSpecificationInterface $specification;

    private $callable;

    public function __construct(CallbackSpecificationInterface $callbackSpecification, ?callable $callable = null)
    {
        $this->specification = $callbackSpecification;
        $this->callable = $callable;
    }

    public function getSpecification(): CallbackSpecificationInterface
    {
        return $this->specification;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(TransitionEvent $event): void
    {
        if ($this->specification->isSatisfiedBy($event)) {
            $this->call($event->getStateMachine()->getObject(), $event);
        }
    }

    protected function call($object, TransitionEvent $event)
    {
        return call_user_func($this->callable, $object, $event);
    }
}
