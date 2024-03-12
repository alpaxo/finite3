<?php

declare(strict_types=1);

namespace Finite\Transition;

use Finite\PropertiesAwareInterface;

/**
 * Interface for transition with properties
 *
 * @author Yohan Giarelli <yohan@giarel.li>
 */
interface PropertiesAwareTransitionInterface extends TransitionInterface, PropertiesAwareInterface
{
    /**
     * Returns an array with resolved properties of transition at the moment
     * it is applied. It's a merge between default properties and "at-apply" properties.
     *
     * @throws \Finite\Exception\TransitionException
     */
    public function resolveProperties(array $properties): array;
}
