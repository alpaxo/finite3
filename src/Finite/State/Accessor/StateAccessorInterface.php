<?php

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
     * @param object $object
     *
     * @return string|null
     * @throws \Finite\Exception\NoSuchPropertyException
     *
     */
    public function getState($object): ?string;

    /**
     * Set the state of the object to the given property path.
     *
     * @param object $object
     * @param string $value
     *
     * @throws NoSuchPropertyException
     */
    public function setState(&$object, $value): void;
}
