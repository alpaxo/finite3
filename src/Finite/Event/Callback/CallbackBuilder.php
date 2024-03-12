<?php

declare(strict_types=1);

namespace Finite\Event\Callback;

use Finite\StateMachine\StateMachineInterface;

/**
 * Builds a Callback instance.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class CallbackBuilder
{
    private StateMachineInterface $stateMachine;

    private array $from;

    private array $to;

    private array $on;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(StateMachineInterface $sm, array $from = [], array $to = [], array $on = [], ?callable $callable = null)
    {
        $this->stateMachine = $sm;
        $this->from = $from;
        $this->to = $to;
        $this->on = $on;
        $this->callable = $callable;
    }

    public function setFrom(array $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function setTo(array $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function setOn(array $on): static
    {
        $this->on = $on;

        return $this;
    }

    public function setCallable($callable): static
    {
        $this->callable = $callable;

        return $this;
    }

    public function addFrom($from): static
    {
        $this->from[] = $from;

        return $this;
    }

    public function addTo($to): static
    {
        $this->to[] = $to;

        return $this;
    }

    public function addOn($on): static
    {
        $this->from[] = $on;

        return $this;
    }

    public function getCallback(): Callback
    {
        return new Callback(
            new CallbackSpecification($this->stateMachine, $this->from, $this->to, $this->on),
            $this->callable
        );
    }

    public static function create(StateMachineInterface $sm, array $from = [], array $to = [], array $on = [], ?callable $callable = null): CallbackBuilder
    {
        return new self($sm, $from, $to, $on, $callable);
    }
}
