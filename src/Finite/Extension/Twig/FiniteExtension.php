<?php

declare(strict_types=1);

namespace Finite\Extension\Twig;

use Finite\Context;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The Finite Twig extension.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class FiniteExtension extends AbstractExtension
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('finite_state', [$this, 'getFiniteState']),
            new TwigFunction('finite_transitions', [$this, 'getFiniteTransitions']),
            new TwigFunction('finite_properties', [$this, 'getFiniteProperties']),
            new TwigFunction('finite_has', [$this, 'hasFiniteProperty']),
            new TwigFunction('finite_can', [$this, 'canFiniteTransition']),
        ];
    }

    public function getFiniteState(object $object, string $graph = 'default'): string
    {
        return $this->context->getState($object, $graph);
    }

    /**
     * @throws \Finite\Exception\TransitionException
     */
    public function getFiniteTransitions(object $object, string $graph = 'default', bool $asObject = false): array
    {
        return $this->context->getTransitions($object, $graph, $asObject);
    }

    public function getFiniteProperties(object $object, string $graph = 'default'): array
    {
        return $this->context->getProperties($object, $graph);
    }

    public function hasFiniteProperty(object $object, string $property, string $graph = 'default'): bool
    {
        return $this->context->hasProperty($object, $property, $graph);
    }

    public function canFiniteTransition(object $object, string $transition, string $graph = 'default'): bool
    {
        return $this->context->getStateMachine($object, $graph)->can($transition);
    }

    public function getName(): string
    {
        return 'finite';
    }
}
