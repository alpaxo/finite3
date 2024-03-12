<?php

declare(strict_types=1);

namespace Finite\Transition;

use Closure;
use Finite\Exception\TransitionException;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachineInterface;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The base Transition class.
 * Feel free to extend it to fit to your needs.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 * @author Michal Dabrowski <dabrowski@brillante.pl>
 */
class Transition implements PropertiesAwareTransitionInterface
{
    protected array $initialStates;

    protected string $state;

    protected string $name;

    protected Closure|array|null $guard;

    protected OptionsResolver $propertiesOptionsResolver;

    public function __construct(
        string $name,
        array|string|int $initialStates,
        string|int $state,
        Closure|array|null $guard = null,
        OptionsResolver $propertiesOptionsResolver = null
    ) {
        if (null !== $guard && !is_callable($guard)) {
            throw new InvalidArgumentException('Invalid callable guard argument passed to Transition::__construct().');
        }

        $this->name = $name;
        $this->state = (string)$state;
        $this->initialStates = array_map('strval', (array)$initialStates);
        $this->guard = $guard;
        $this->propertiesOptionsResolver = $propertiesOptionsResolver ?: new OptionsResolver();
    }

    public function addInitialState(StateInterface|string|int $state): void
    {
        if ($state instanceof StateInterface) {
            $state = $state->getName();
        }

        $this->initialStates[] = (string)$state;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialStates(): array
    {
        return $this->initialStates;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function process(StateMachineInterface $stateMachine): mixed
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return callable|null
     */
    public function getGuard(): ?callable
    {
        return $this->guard;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveProperties(array $properties): array
    {
        try {
            return $this->propertiesOptionsResolver->resolve($properties);
        } catch (MissingOptionsException $e) {
            throw new TransitionException(
                'Testing or applying this transition need a parameter. Provide it or set it optional.',
                $e->getCode(),
                $e
            );
        } catch (UndefinedOptionsException $e) {
            throw new TransitionException(
                'You provided an unknown property to test() or apply(). Remove it or declare it in your graph.',
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $property): bool
    {
        return array_key_exists($property, $this->getProperties());
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $property, mixed $default = null): mixed
    {
        $properties = $this->getProperties();

        return $this->has($property) ? $properties[$property] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties(): array
    {
        $missingOptions = $this->propertiesOptionsResolver->getMissingOptions();

        if (0 === count($missingOptions)) {
            return $this->propertiesOptionsResolver->resolve();
        }

        $options = array_combine($missingOptions, array_fill(0, count($missingOptions), null));

        return array_diff_key(
            $this->propertiesOptionsResolver->resolve($options),
            array_combine($missingOptions, $missingOptions)
        );
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
