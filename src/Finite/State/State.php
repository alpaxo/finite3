<?php

namespace Finite\State;

use Finite\Transition\TransitionInterface;

/**
 * The base State class.
 * Feel free to extend it to fit to your needs.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 * @author Michal Dabrowski <dabrowski@brillante.pl>
 */
class State implements StateInterface
{
    /**
     * The state type.
     *
     * @var int
     */
    protected $type;

    /**
     * The transition name.
     *
     * @var array
     */
    protected $transitions;

    /**
     * The state name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $properties;

    public function __construct($name, $type = self::TYPE_NORMAL, array $transitions = [], array $properties = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->transitions = $transitions;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function isInitial(): bool
    {
        return self::TYPE_INITIAL === $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal(): bool
    {
        return self::TYPE_FINAL === $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function isNormal(): bool
    {
        return self::TYPE_NORMAL === $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param $transition
     */
    public function addTransition($transition): void
    {
        if ($transition instanceof TransitionInterface) {
            $transition = $transition->getName();
        }

        $this->transitions[] = $transition;
    }

    /**
     * @param array $transitions
     */
    public function setTransitions(array $transitions): void
    {
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since version 1.0.0-BETA2. Use {@link StateMachine::can($transition)} instead.
     */
    public function can($transition): bool
    {
        if ($transition instanceof TransitionInterface) {
            $transition = $transition->getName();
        }

        return in_array($transition, $this->transitions, true);
    }

    /**
     * {@inheritdoc}
     */
    public function has($property): bool
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function get($property, $default = null)
    {
        return $this->has($property) ? $this->properties[$property] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}