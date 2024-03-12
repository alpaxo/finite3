<?php

declare(strict_types=1);

namespace Finite\State\Accessor;

use Finite\Exception\NoSuchPropertyException;

/**
 * Base interface for state accessors.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface StateAccessorInterface
{
    /**
     * Retrieves the current state from the given object.
     *
     * @throws \Finite\Exception\NoSuchPropertyException
     */
    public function getState(object $object): ?string;

    /**
     * Set the state of the object to the given property path.
     *
     * @throws NoSuchPropertyException
     */
    public function setState(object $object, string $value): void;
}
